<?php

namespace App\Filament\Widgets;

use App\Models\PythonApiComparison;
use Filament\Widgets\Widget;

class ComparisonAccuracyGauge extends Widget
{
    protected static string $view = 'filament.widgets.comparison-accuracy-gauge';
    protected static ?int $sort = 40;

    public function getAccuracyData(): array
    {
        try {
            // Gesamtanzahl der Vergleiche
            $totalComparisons = PythonApiComparison::count();
            
            // Erfolgreiche Übereinstimmungen
            $successfulComparisons = PythonApiComparison::where('overall_status', 'matched')->count();
            
            // Abweichungen
            $mismatchedComparisons = PythonApiComparison::where('overall_status', 'mismatched')->count();
            
            // Prüfung erforderlich
            $needsReviewComparisons = PythonApiComparison::where('overall_status', 'needs_review')->count();
            
            // Ausstehend
            $pendingComparisons = PythonApiComparison::where('overall_status', 'pending')->count();
            
            // Genauigkeitsrate berechnen
            $accuracyRate = $totalComparisons > 0 ? ($successfulComparisons / $totalComparisons) * 100 : 0;
            
            // Durchschnittliche Übereinstimmungen pro Vergleich
            $avgMatches = PythonApiComparison::where('overall_status', 'matched')
                ->avg('matches') ?? 0;
                
            // Durchschnittliche Abweichungen pro Vergleich
            $avgMismatches = PythonApiComparison::where('overall_status', 'mismatched')
                ->avg('mismatches') ?? 0;
            
        } catch (\Exception $e) {
            $totalComparisons = 0;
            $successfulComparisons = 0;
            $mismatchedComparisons = 0;
            $needsReviewComparisons = 0;
            $pendingComparisons = 0;
            $accuracyRate = 0;
            $avgMatches = 0;
            $avgMismatches = 0;
        }

        return [
            'total_comparisons' => $totalComparisons,
            'successful_comparisons' => $successfulComparisons,
            'mismatched_comparisons' => $mismatchedComparisons,
            'needs_review_comparisons' => $needsReviewComparisons,
            'pending_comparisons' => $pendingComparisons,
            'accuracy_rate' => round($accuracyRate, 1),
            'avg_matches' => round($avgMatches, 1),
            'avg_mismatches' => round($avgMismatches, 1),
        ];
    }
}
