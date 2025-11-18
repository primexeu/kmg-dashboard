<?php

namespace App\Filament\Widgets;

use App\Models\PythonApiComparison;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class KuechenabgleichStats extends BaseWidget
{
    protected static ?int $sort = 10;

    protected function getStats(): array
    {
        try {
            // Heute verarbeitete Vergleiche
            $heuteVerarbeitet = PythonApiComparison::whereDate('processed_at', today())->count();
            
            // Diese Woche verarbeitete Vergleiche
            $dieseWoche = PythonApiComparison::whereBetween('processed_at', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ])->count();
            
            // Erfolgreiche Übereinstimmungen heute
            $erfolgreichHeute = PythonApiComparison::whereDate('processed_at', today())
                ->where('overall_status', 'matched')
                ->count();
            
            // Erfolgsrate heute
            $erfolgsrateHeute = $heuteVerarbeitet > 0 ? ($erfolgreichHeute / $heuteVerarbeitet) * 100 : 0;
            
            // Durchschnittliche Verarbeitungszeit (in Minuten)
            $avgVerarbeitungszeit = PythonApiComparison::whereDate('processed_at', today())
                ->whereNotNull('processed_at')
                ->select(DB::raw('AVG(TIMESTAMPDIFF(MINUTE, created_at, processed_at)) as avg_time'))
                ->value('avg_time') ?? 0;
            
            // Gesamtanzahl Vergleiche
            $gesamtVergleiche = PythonApiComparison::count();
            
        } catch (\Exception $e) {
            $heuteVerarbeitet = 0;
            $dieseWoche = 0;
            $erfolgreichHeute = 0;
            $erfolgsrateHeute = 0;
            $avgVerarbeitungszeit = 0;
            $gesamtVergleiche = 0;
        }

        return [
            Stat::make('Heute verarbeitet', $heuteVerarbeitet)
                ->description('Küchenabgleiche heute')
                ->descriptionIcon('heroicon-m-document-magnifying-glass')
                ->color('primary'),
                
            Stat::make('Erfolgsrate heute', number_format($erfolgsrateHeute, 1) . '%')
                ->description($erfolgreichHeute . ' von ' . $heuteVerarbeitet . ' erfolgreich')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color($erfolgsrateHeute >= 80 ? 'success' : ($erfolgsrateHeute >= 60 ? 'warning' : 'danger')),
                
            Stat::make('Diese Woche', $dieseWoche)
                ->description('Küchenabgleiche diese Woche')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('info'),
                
            Stat::make('Durchschnittliche Zeit', $avgVerarbeitungszeit > 0 ? number_format($avgVerarbeitungszeit, 1) . ' Min' : 'N/A')
                ->description('Verarbeitungszeit heute')
                ->descriptionIcon('heroicon-m-clock')
                ->color('gray'),
        ];
    }
}
