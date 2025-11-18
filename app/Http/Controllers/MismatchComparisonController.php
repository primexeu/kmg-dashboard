<?php

namespace App\Http\Controllers;

use App\Models\OrderMismatch;
use Illuminate\Http\Request;

class MismatchComparisonController extends Controller
{
    public function show(Request $request, $mismatchId)
    {
        $mismatch = OrderMismatch::findOrFail($mismatchId);
        
        // Extract the full payload from the details
        $details = $mismatch->details;
        if (is_string($details)) {
            $details = json_decode($details, true);
        }
        $payload = $details['full_payload'] ?? [];
        $mismatchDetails = $details['mismatches'] ?? [];
        
        // Extract PDF and ERP data for side-by-side comparison
        $pdfData = [];
        $erpData = [];
        
        $results = $payload['results'] ?? $payload['outcome'] ?? $payload;
        
        // Extract PDF data
        $pdfData = [
            'header' => $results['pdf_header'] ?? [],
            'groups' => $results['pdf_groups'] ?? [],
            'items' => []
        ];
        
        // Flatten PDF items for easier comparison
        foreach ($pdfData['groups'] as $group) {
            foreach ($group['items'] ?? [] as $item) {
                $pdfData['items'][$item['reference']] = $item;
            }
        }
        
        // Extract ERP data
        $erpData = [
            'items' => []
        ];
        
        $matchResults = $results['match_results'] ?? [];
        foreach ($matchResults as $matchResult) {
            foreach ($matchResult['excel_items'] ?? [] as $item) {
                $erpData['items'][$item['reference']] = $item;
            }
        }
        
        return view('filament.resources.order-mismatch-resource.pages.mismatch-comparison', [
            'mismatch' => $mismatch,
            'payload' => $payload,
            'mismatchDetails' => $mismatchDetails,
            'pdfData' => $pdfData,
            'erpData' => $erpData,
        ]);
    }
    
    public function getMismatchType($erpItem, $pdfItem): string
    {
        $mismatches = [];
        
        if (!$pdfItem) {
            $mismatches[] = 'missing_pdf';
        }

        if (!$erpItem) {
            $mismatches[] = 'missing_erp';
        }

        if ($erpItem && $pdfItem) {
            // Check for quantity mismatch
            if (isset($erpItem['qty']) && isset($pdfItem['qty']) && $erpItem['qty'] != $pdfItem['qty']) {
                $mismatches[] = 'quantity_mismatch';
            }

            // Check for price mismatch
            if (isset($erpItem['unit_price']) && isset($pdfItem['unit_price']) && 
                abs($erpItem['unit_price'] - $pdfItem['unit_price']) > 0.01) {
                $mismatches[] = 'price_mismatch';
            }

            // Check for delivery week mismatch
            if (isset($erpItem['delivery_week']) && isset($pdfItem['delivery_week']) && 
                $erpItem['delivery_week'] != $pdfItem['delivery_week']) {
                $mismatches[] = 'delivery_week_mismatch';
            }
        }

        if (empty($mismatches)) {
            return 'match';
        }

        return implode(', ', $mismatches);
    }
}
