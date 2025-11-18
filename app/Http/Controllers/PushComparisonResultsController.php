<?php

namespace App\Http\Controllers;

use App\Models\OrderMatch as MatchModel; // Eloquent model
use App\Models\PythonApiComparison;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PushComparisonResultsController extends Controller
{
    public function __invoke(Request $request)
    {
        // Accept JSON either in request body or in form fields
        $payload = $request->input();
        if (empty($payload)) {
            $payload = json_decode($request->getContent(), true) ?: [];
        }

        $metadata = $payload['metadata'] ?? $payload['data']['metadata'] ?? [];
        $hasComparisonId = isset($metadata['comparison_id']) || isset($payload['metadata']['comparison_id']) || isset($payload['data']['metadata']['comparison_id']);
        
        Log::info('PushComparisonResultsController received payload', [
            'payload_keys' => array_keys($payload),
            'has_order_header' => isset($payload['order_header']),
            'has_ab_header' => isset($payload['ab_header']),
            'has_summary' => isset($payload['summary']),
            'has_files' => isset($payload['files']) || isset($payload['data']['files']),
            'has_metadata' => !empty($metadata),
            'has_comparison_id' => $hasComparisonId,
            'comparison_id' => $metadata['comparison_id'] ?? $payload['metadata']['comparison_id'] ?? $payload['data']['metadata']['comparison_id'] ?? 'MISSING',
            'files_location' => isset($payload['files']) ? 'root' : (isset($payload['data']['files']) ? 'data.files' : 'none'),
        ]);
        
        // Warning if comparison_id is missing (for debugging)
        if (!$hasComparisonId) {
            Log::warning('PushComparisonResultsController: No comparison_id in metadata. Files may not link correctly!', [
                'order_number' => $payload['order_header']['bestellung'] ?? $payload['data']['order_header']['bestellung'] ?? 'unknown',
                'ab_number' => $payload['ab_header']['ab_nr'] ?? $payload['data']['ab_header']['ab_nr'] ?? 'unknown',
            ]);
        }

        // Check if this is a Python API comparison result (new format)
        if (isset($payload['order_header']) && isset($payload['ab_header']) && isset($payload['summary'])) {
            return $this->processPythonApiResult($payload);
        }

        // Legacy processing for old format
        $comparison = $payload['results'] ?? $payload['outcome'] ?? $payload;
        $pdfHeader  = $comparison['pdf_header'] ?? [];
        $poNumber   = $pdfHeader['po_number'] ?? null;
        $customer   = $pdfHeader['customer'] ?? null;

        // Find or create order and order confirmation for legacy format
        // order_confirmation_id is required (NOT NULL), so we must create it
        $poNumber = $poNumber ?? 'UNKNOWN-' . uniqid(); // Fallback if no PO number
        
        $order = \App\Models\Order::firstOrCreate(
            ['po_number' => $poNumber],
            [
                'order_number' => $poNumber,
                'supplier' => $customer ?? 'Unknown',
                'status' => 'open',
            ]
        );
        
        $orderConfirmation = \App\Models\OrderConfirmation::firstOrCreate(
            ['order_id' => $order->id],
            [
                'supplier' => $customer ?? 'Unknown',
                'received_at' => now(),
                'confidence' => 0.95,
                'status' => 'confirmed',
            ]
        );

        // Create match record (adjust columns to your schema)
        // Note: order_confirmation_id is required (NOT NULL), so we always create order confirmation above
        $match = MatchModel::create([
            'order_confirmation_id' => $orderConfirmation->id, // Required field - always set
            'order_id' => $order?->id ?? null,
            'po_number'  => $poNumber,
            'customer'   => $customer,
            'status'     => 'processed',
            'result'     => 'matched', // Required field - set to matched since API processed successfully
            'matched_at' => now(),
            'payload'    => $payload,
            // KMG convention: use Auth::id() for author/updater
            'author_id'  => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        // Stash in session for the dashboard widget preview
        session([
            'comparison.results'  => $payload,
            'comparison.match_id' => $match->getKey(),
        ]);

        return response()->json(['status' => 'processed', 'match_id' => $match->id]);
    }

    /**
     * Process Python API comparison results and create PythonApiComparison record
     */
    private function processPythonApiResult(array $data)
    {
        try {
            Log::info('Processing Python API result in PushComparisonResultsController', [
                'data_keys' => array_keys($data)
            ]);

            // Extract basic information for identification
            $orderHeader = $data['order_header'] ?? [];
            $abHeader = $data['ab_header'] ?? [];
            $summary = $data['summary'] ?? [];

            // Get basic identifiers
            $orderNumber = $orderHeader['bestellung'] ?? 'Unknown';
            $abNumber = $abHeader['ab_nr'] ?? 'Unknown';
            $customerNumber = $orderHeader['kunden_nr'] ?? null;
            $customerName = $abHeader['kunde'] ?? null;
            $commission = $orderHeader['kommission'] ?? $abHeader['kommission'] ?? null;

            // Determine overall status based on summary
            $overallStatus = 'pending';
            $headerVerdict = $data['header_comparison']['overall']['verdict'] ?? '';

            // Safely access summary values with defaults
            $matches = $summary['matches'] ?? 0;
            $mismatches = $summary['mismatches'] ?? 0;
            $reviewRequired = $summary['review_required'] ?? 0;
            $totalItems = $summary['total_items'] ?? 0;
            $missingInOrder = $summary['missing_in_order'] ?? 0;
            $missingInConfirmation = $summary['missing_in_confirmation'] ?? 0;

            if ($matches > 0 && $mismatches == 0 && $reviewRequired == 0) {
                $overallStatus = 'matched';
            } elseif ($mismatches > 0 || $reviewRequired > 0 || in_array($headerVerdict, ['Prüfung erforderlich', 'Keine Übereinstimmung'])) {
                $overallStatus = 'mismatched';
            } elseif ($reviewRequired > 0 || $headerVerdict === 'Prüfung erforderlich') {
                $overallStatus = 'needs_review';
            }

            // Check if this comparison already exists (avoid duplicates)
            $existingComparison = PythonApiComparison::where('order_number', $orderNumber)
                ->where('ab_number', $abNumber)
                ->where('processed_at', '>=', now()->subMinutes(5)) // Within last 5 minutes
                ->first();

            if ($existingComparison) {
                Log::info('Comparison already exists, updating existing record', [
                    'comparison_id' => $existingComparison->id
                ]);

                // Update existing record
                $existingComparison->update([
                    'order_header' => $orderHeader,
                    'ab_header' => $abHeader,
                    'header_comparison' => $data['header_comparison'] ?? [],
                    'items_comparison' => $data['items_comparison'] ?? [],
                    'summary' => $summary,
                    'full_payload' => $data,
                    'overall_status' => $overallStatus,
                    'total_items' => $totalItems,
                    'matches' => $matches,
                    'mismatches' => $mismatches,
                    'review_required' => $reviewRequired,
                    'missing_in_order' => $missingInOrder,
                    'missing_in_confirmation' => $missingInConfirmation,
                    'processed_at' => now(),
                    'updated_by' => Auth::id(),
                ]);

                return response()->json([
                    'status' => 'updated',
                    'comparison_id' => $existingComparison->id,
                    'type' => 'python_api_comparison'
                ]);
            }

            // Create new PythonApiComparison record
            $comparison = PythonApiComparison::create([
                'order_number' => $orderNumber,
                'ab_number' => $abNumber,
                'customer_number' => $customerNumber,
                'customer_name' => $customerName,
                'commission' => $commission,
                'order_header' => $orderHeader,
                'ab_header' => $abHeader,
                'header_comparison' => $data['header_comparison'] ?? [],
                'items_comparison' => $data['items_comparison'] ?? [],
                'summary' => $summary,
                'full_payload' => $data,
                'overall_status' => $overallStatus,
                'total_items' => $totalItems,
                'matches' => $matches,
                'mismatches' => $mismatches,
                'review_required' => $reviewRequired,
                'missing_in_order' => $missingInOrder,
                'missing_in_confirmation' => $missingInConfirmation,
                'processed_at' => now(),
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);

            Log::info('Created new PythonApiComparison record', [
                'comparison_id' => $comparison->id,
                'order_number' => $orderNumber,
                'ab_number' => $abNumber
            ]);

            return response()->json([
                'status' => 'created',
                'comparison_id' => $comparison->id,
                'type' => 'python_api_comparison'
            ]);

        } catch (\Exception $e) {
            Log::error('Error processing Python API result in PushComparisonResultsController', [
                'error' => $e->getMessage(),
                'data_structure' => array_keys($data)
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to process comparison result: ' . $e->getMessage()
            ], 500);
        }
    }
}
