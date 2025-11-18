<?php

namespace App\Filament\Widgets;

use App\Models\PythonApiComparison;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class ComparisonStatusOverview extends ChartWidget
{
    protected static ?string $heading = 'Küchenabgleich Status (Letzte 7 Tage)';
    protected static ?int $sort = 30;

    protected function getData(): array
    {
        $labels = [];
        $matchedData = [];
        $mismatchedData = [];
        $needsReviewData = [];
        $pendingData = [];

        try {
            for ($i = 6; $i >= 0; $i--) {
                $day = Carbon::today()->subDays($i);
                $labels[] = $day->format('d.m');
                
                $matchedData[] = PythonApiComparison::whereDate('processed_at', $day)
                    ->where('overall_status', 'matched')
                    ->count();
                    
                $mismatchedData[] = PythonApiComparison::whereDate('processed_at', $day)
                    ->where('overall_status', 'mismatched')
                    ->count();
                    
                $needsReviewData[] = PythonApiComparison::whereDate('processed_at', $day)
                    ->where('overall_status', 'needs_review')
                    ->count();
                    
                $pendingData[] = PythonApiComparison::whereDate('processed_at', $day)
                    ->where('overall_status', 'pending')
                    ->count();
            }
        } catch (\Exception $e) {
            $labels = [];
            $matchedData = [];
            $mismatchedData = [];
            $needsReviewData = [];
            $pendingData = [];
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Übereinstimmungen',
                    'data' => $matchedData,
                    'backgroundColor' => 'rgba(34, 197, 94, 0.8)',
                    'borderColor' => 'rgb(34, 197, 94)',
                    'borderWidth' => 1,
                ],
                [
                    'label' => 'Keine Übereinstimmung',
                    'data' => $mismatchedData,
                    'backgroundColor' => 'rgba(239, 68, 68, 0.8)',
                    'borderColor' => 'rgb(239, 68, 68)',
                    'borderWidth' => 1,
                ],
                [
                    'label' => 'Prüfung erforderlich',
                    'data' => $needsReviewData,
                    'backgroundColor' => 'rgba(245, 158, 11, 0.8)',
                    'borderColor' => 'rgb(245, 158, 11)',
                    'borderWidth' => 1,
                ],
                [
                    'label' => 'Ausstehend',
                    'data' => $pendingData,
                    'backgroundColor' => 'rgba(107, 114, 128, 0.8)',
                    'borderColor' => 'rgb(107, 114, 128)',
                    'borderWidth' => 1,
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'stacked' => true,
                ],
                'x' => [
                    'stacked' => true,
                ],
            ],
            'plugins' => [
                'legend' => [
                    'position' => 'top',
                ],
            ],
        ];
    }
}
