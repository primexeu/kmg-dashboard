<?php

// app/Filament/Pages/Dashboard.php
namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use App\Filament\Widgets\{
    KuechenabgleichStats,
    RecentComparisons,
    ComparisonStatusOverview,
    ComparisonAccuracyGauge,
};

class Dashboard extends BaseDashboard
{
    // 12-col grid
    public function getColumns(): int|array
    {
        return 12;
    }

    // Header widgets - Küchenabgleich statistics at the top
    public function getHeaderWidgets(): array
    {
        return [
            KuechenabgleichStats::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 12;
    }

    // Main dashboard widgets
    public function getWidgets(): array
    {
        return [
            // Row 1: Recent comparisons table (full width)
            RecentComparisons::class,
            
            // Row 2: Status overview chart + Accuracy gauge
            ComparisonStatusOverview::class,
            ComparisonAccuracyGauge::class,
        ];
    }
}
