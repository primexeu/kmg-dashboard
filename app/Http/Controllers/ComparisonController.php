<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ComparisonController extends Controller
{

    /**
     * Sanitize NaN values in payload data
     */
    private function sanitizeNanValues($data)
    {
        if (is_array($data)) {
            return array_map([$this, 'sanitizeNanValues'], $data);
        } elseif (is_string($data) && $data === 'NaN') {
            return null; // Convert NaN string to null
        } elseif (is_float($data) && is_nan($data)) {
            return null; // Convert NaN float to null
        }
        return $data;
    }

    /**
     * Debug endpoint to test API connection and see raw response
     */
    public function debugApiConnection()
    {
        try {
            $apiUrl = config('services.python.api_url', env('PYTHON_API_URL', 'http://localhost:5000'));
            
            $response = Http::timeout(30)->get($apiUrl . '/api/results');
            
            return response()->json([
                'success' => true,
                'api_url' => $apiUrl . '/api/results',
                'status' => $response->status(),
                'headers' => $response->headers(),
                'raw_body' => $response->body(),
                'parsed_json' => $response->json(),
                'data_structure' => array_keys($response->json() ?? []),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * Check for new results from Python API and process them automatically
     */
    public function checkForNewResults()
    {
        try {
            $apiUrl = config('services.python.api_url', env('PYTHON_API_URL', 'http://localhost:5000'));
            
            Log::info('Checking for new API results', ['api_url' => $apiUrl . '/api/results']);
            
            $response = Http::timeout(10)->get($apiUrl . '/api/results');
            
            if ($response->status() === 200) {
                $data = $response->json();
                
                // Check if we already have this result
                $orderHeader = $data['order_header'] ?? [];
                $abHeader = $data['ab_header'] ?? [];
                $orderNumber = $orderHeader['bestellung'] ?? 'Unknown';
                $abNumber = $abHeader['ab_nr'] ?? 'Unknown';
                
                $existingComparison = \App\Models\PythonApiComparison::where('order_number', $orderNumber)
                    ->where('ab_number', $abNumber)
                    ->where('processed_at', '>=', now()->subMinutes(10)) // Within last 10 minutes
                    ->first();
                
                if (!$existingComparison) {
                    // Process the new results
                    $result = $this->processApiResults($data);
                    
                    return response()->json([
                        'success' => true,
                        'new_result' => true,
                        'database_result' => $result
                    ]);
                } else {
                    return response()->json([
                        'success' => true,
                        'new_result' => false,
                        'message' => 'Result already exists'
                    ]);
                }
            } elseif ($response->status() === 202) {
                return response()->json([
                    'success' => false,
                    'message' => 'Processing in progress',
                    'status' => $response->json()
                ], 202);
            } elseif ($response->status() === 404) {
                return response()->json([
                    'success' => false,
                    'message' => 'No results available'
                ], 404);
            } else {
                Log::error('API returned error status during check', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'API returned error status: ' . $response->status()
                ], $response->status());
            }
        } catch (\Exception $e) {
            Log::error('Error checking for new API results', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fetch results from Python API and create comparison record
     */
    public function fetchApiResults()
    {
        try {
            $apiUrl = config('services.python.api_url', env('PYTHON_API_URL', 'http://localhost:5000'));
            
            Log::info('Fetching API results', ['api_url' => $apiUrl . '/api/results']);
            
            $response = Http::timeout(30)->get($apiUrl . '/api/results');
            
            Log::info('API response received', [
                'status' => $response->status(),
                'headers' => $response->headers(),
                'body_preview' => substr($response->body(), 0, 500)
            ]);
            
            if ($response->status() === 200) {
                $data = $response->json();
                
                Log::info('API response parsed', [
                    'data_structure' => array_keys($data),
                    'has_summary' => isset($data['summary']),
                    'summary_keys' => isset($data['summary']) ? array_keys($data['summary']) : 'No summary'
                ]);
                
                // Check for duplicates before processing
                $duplicateCheck = $this->checkForDuplicateResults($data);
                if ($duplicateCheck['is_duplicate']) {
                    return response()->json([
                        'success' => false,
                        'message' => 'These results have already been processed',
                        'existing_record' => $duplicateCheck['existing_record'],
                        'is_duplicate' => true
                    ], 200);
                }
                
                // Process the API results and create database records
                $result = $this->processApiResults($data);
                
                return response()->json([
                    'success' => true,
                    'data' => $data,
                    'database_result' => $result,
                    'is_duplicate' => false
                ]);
            } elseif ($response->status() === 202) {
                return response()->json([
                    'success' => false,
                    'message' => 'Processing in progress',
                    'status' => $response->json()
                ], 202);
            } elseif ($response->status() === 404) {
                return response()->json([
                    'success' => false,
                    'message' => 'No results available'
                ], 404);
            } else {
                Log::error('API returned error status', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to get results - Status: ' . $response->status()
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Exception in fetchApiResults', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Connection failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process API results and create database records
     */
    private function processApiResults(array $data)
    {
        try {
            // Log the incoming data for debugging
            Log::info('Processing API results', ['data_keys' => array_keys($data)]);
            
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
            
            // Create PythonApiComparison record - store everything as-is
            $comparison = \App\Models\PythonApiComparison::create([
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
            
            return [
                'type' => 'python_api_comparison',
                'comparison_id' => $comparison->id,
                'redirect_url' => \App\Filament\Resources\PythonApiComparisonResource\Pages\ComparePythonApiResults::getUrl(['record' => $comparison->id])
            ];
            
        } catch (\Exception $e) {
            Log::error('Error processing API results', [
                'error' => $e->getMessage(),
                'data_structure' => array_keys($data),
                'summary_keys' => isset($data['summary']) ? array_keys($data['summary']) : 'No summary key'
            ]);
            
            throw new \Exception('Failed to process API results: ' . $e->getMessage());
        }
    }

    /**
     * Check if the API results have already been processed
     */
    private function checkForDuplicateResults(array $data): array
    {
        try {
            // Extract basic information for identification
            $orderHeader = $data['order_header'] ?? [];
            $abHeader = $data['ab_header'] ?? [];
            $summary = $data['summary'] ?? [];
            
            // Get basic identifiers
            $orderNumber = $orderHeader['bestellung'] ?? 'Unknown';
            $abNumber = $abHeader['ab_nr'] ?? 'Unknown';
            
            // Create a hash of the full payload for more accurate duplicate detection
            $payloadHash = md5(json_encode($data));
            
            // Check for existing record with same order/ab numbers and similar processing time
            $existingComparison = \App\Models\PythonApiComparison::where('order_number', $orderNumber)
                ->where('ab_number', $abNumber)
                ->where('processed_at', '>=', now()->subHours(24)) // Within last 24 hours
                ->first();
            
            if ($existingComparison) {
                // Also check if the payload content is the same
                $existingPayloadHash = md5(json_encode($existingComparison->full_payload));
                
                if ($payloadHash === $existingPayloadHash) {
                    Log::info('Duplicate results detected', [
                        'order_number' => $orderNumber,
                        'ab_number' => $abNumber,
                        'existing_id' => $existingComparison->id,
                        'processed_at' => $existingComparison->processed_at
                    ]);
                    
                    return [
                        'is_duplicate' => true,
                        'existing_record' => [
                            'id' => $existingComparison->id,
                            'order_number' => $existingComparison->order_number,
                            'ab_number' => $existingComparison->ab_number,
                            'processed_at' => $existingComparison->processed_at,
                            'overall_status' => $existingComparison->overall_status
                        ]
                    ];
                }
            }
            
            return [
                'is_duplicate' => false,
                'existing_record' => null
            ];
            
        } catch (\Exception $e) {
            Log::error('Error checking for duplicate results', [
                'error' => $e->getMessage()
            ]);
            
            // If there's an error checking, assume it's not a duplicate to be safe
            return [
                'is_duplicate' => false,
                'existing_record' => null
            ];
        }
    }
}
    