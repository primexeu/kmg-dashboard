<?php

namespace App\Http\Controllers;

use App\Models\PythonApiComparison;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class PythonApiComparisonController extends Controller
{
    /**
     * Download PDF summary of comparison
     */
    public function downloadSummary($id)
    {
        $comparison = PythonApiComparison::findOrFail($id);
        
        // Get all the data we need
        $payload = $comparison->full_payload ?? [];
        $data = $payload['data'] ?? $payload;
        
        $summary = $data['summary'] ?? [];
        $orderHeader = $data['order_header'] ?? [];
        $abHeader = $data['ab_header'] ?? [];
        $headerComparison = $data['header_comparison'] ?? [];
        $itemsComparison = $data['items_comparison'] ?? [];
        
        // Calculate statistics
        $totalItems = $summary['total_items'] ?? 0;
        $itemsComparisonArray = is_array($itemsComparison) ? $itemsComparison : [];
        $groupedItems = collect($itemsComparisonArray)->groupBy('verdict');
        
        $matched = $groupedItems->get('Übereinstimmung', collect())->count();
        $mismatched = $groupedItems->get('Keine Übereinstimmung', collect())->count();
        $review = $groupedItems->get('Prüfung erforderlich', collect())->count();
        $missingOrder = $groupedItems->get('Fehlt in Bestellung', collect())->count();
        $missingAB = $groupedItems->get('Fehlt in Auftragsbestätigung', collect())->count();
        $totalItemsComparison = count($itemsComparisonArray);
        
        // Header comparison stats
        $headerRows = $headerComparison['rows'] ?? [];
        $headerGrouped = collect($headerRows)->groupBy('verdict');
        $headerMatched = $headerGrouped->get('Übereinstimmung', collect())->count();
        $headerMismatched = $headerGrouped->get('Keine Übereinstimmung', collect())->count();
        $headerReview = $headerGrouped->get('Prüfung erforderlich', collect())->count();
        $headerTotal = count($headerRows);
        
        // Generate PDF using DomPDF
        $pdf = Pdf::loadView('pdf.comparison-summary', [
            'comparison' => $comparison,
            'orderHeader' => $orderHeader,
            'abHeader' => $abHeader,
            'summary' => $summary,
            'totalItems' => $totalItems,
            'matched' => $matched,
            'mismatched' => $mismatched,
            'review' => $review,
            'missingOrder' => $missingOrder,
            'missingAB' => $missingAB,
            'totalItemsComparison' => $totalItemsComparison,
            'headerMatched' => $headerMatched,
            'headerMismatched' => $headerMismatched,
            'headerReview' => $headerReview,
            'headerTotal' => $headerTotal,
            'headerComparison' => $headerComparison,
            'itemsComparison' => $itemsComparisonArray,
        ]);
        
        // Set PDF options
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', true);
        
        // Generate filename
        $filename = 'Vergleichs-Zusammenfassung-' . $comparison->id . '-' . now()->format('Y-m-d') . '.pdf';
        
        // Return PDF download
        return $pdf->download($filename);
    }
}

