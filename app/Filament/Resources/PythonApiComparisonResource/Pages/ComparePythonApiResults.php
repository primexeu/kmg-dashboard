<?php

namespace App\Filament\Resources\PythonApiComparisonResource\Pages;

use App\Filament\Resources\PythonApiComparisonResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\Page;
use Filament\Notifications\Notification;

class ComparePythonApiResults extends Page
{
    protected static string $resource = PythonApiComparisonResource::class;
    protected static string $view = 'filament.resources.python-api-comparison-resource.pages.compare-python-api-results';
    
    protected static ?string $title = 'Ergebnisse des Küchenabgleichs ';
    protected static ?string $navigationIcon = 'heroicon-o-document-magnifying-glass';

    public ?int $comparisonId = null;
    public ?\App\Models\PythonApiComparison $record = null;
    
    /** @var array<string,mixed> */
    public array $orderHeader = [];
    
    /** @var array<string,mixed> */
    public array $abHeader = [];
    
    /** @var array<string,mixed> */
    public array $headerComparison = [];
    
    /** @var array<int, array<string, mixed>> */
    public array $itemsComparison = [];
    
    /** @var array<string,mixed> */
    public array $summary = [];

    public function mount(int|string $record): void
    {
        $this->comparisonId = is_numeric($record) ? (int) $record : null;
        
        if ($this->comparisonId) {
            $this->record = \App\Models\PythonApiComparison::findOrFail($this->comparisonId);
            
            if ($this->record) {
                $this->orderHeader = $this->record->order_header ?? [];
                $this->abHeader = $this->record->ab_header ?? [];
                $this->headerComparison = $this->record->header_comparison ?? [];
                $this->itemsComparison = $this->record->items_comparison ?? [];
                $this->summary = $this->record->summary ?? [];
            }
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadSummary')
                ->label('PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->url(fn () => route('python-api-comparison.download-summary', $this->record->id))
                ->openUrlInNewTab(false),
            
            Action::make('fetchNewResults')
                ->label('Aktualisieren')
                ->url('/fetch-api-results')
                ->icon('heroicon-o-arrow-path')
                ->color('info')
                ->openUrlInNewTab(),

            Action::make('backToList')
                ->label('Zurück zur Liste')
                ->url(static::getResource()::getUrl('index'))
                ->icon('heroicon-o-arrow-left')
                ->color('gray'),
        ];
    }

    /**
     * Get items grouped by verdict
     */
    public function getItemsByVerdict(): array
    {
        $grouped = [
            'Übereinstimmung' => [],
            'Prüfung erforderlich' => [],
            'Keine Übereinstimmung' => [],
            'Fehlt in Bestellung' => [],
            'Fehlt in Auftragsbestätigung' => [],
        ];

        foreach ($this->itemsComparison as $item) {
            $verdict = $item['verdict'] ?? 'Unknown';
            if (isset($grouped[$verdict])) {
                $grouped[$verdict][] = $item;
            }
        }

        return $grouped;
    }

    /**
     * Get header comparison rows grouped by verdict
     */
    public function getHeaderRowsByVerdict(): array
    {
        $grouped = [
            'Übereinstimmung' => [],
            'Prüfung erforderlich' => [],
            'Keine Übereinstimmung' => [],
        ];

        $rows = $this->headerComparison['rows'] ?? [];
        foreach ($rows as $row) {
            $verdict = $row['verdict'] ?? 'Unknown';
            if (isset($grouped[$verdict])) {
                $grouped[$verdict][] = $row;
            }
        }

        return $grouped;
    }

    /**
     * Get verdict badge color
     */
    public function getVerdictColor(string $verdict): string
    {
        return match ($verdict) {
            'Übereinstimmung' => 'success',
            'Prüfung erforderlich' => 'warning',
            'Keine Übereinstimmung' => 'danger',
            'Fehlt in Bestellung' => 'info',
            'Fehlt in Auftragsbestätigung' => 'info',
            default => 'gray',
        };
    }

    /**
     * Get status badge color
     */
    public function getStatusColor(string $status): string
    {
        return match ($status) {
            'matched' => 'success',
            'mismatched' => 'danger',
            'needs_review' => 'warning',
            'pending' => 'gray',
            default => 'gray',
        };
    }
}
