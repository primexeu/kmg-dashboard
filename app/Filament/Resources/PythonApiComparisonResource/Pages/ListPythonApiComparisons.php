<?php

namespace App\Filament\Resources\PythonApiComparisonResource\Pages;

use App\Filament\Resources\PythonApiComparisonResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPythonApiComparisons extends ListRecords
{
    protected static string $resource = PythonApiComparisonResource::class;

    protected static string $view = 'filament.resources.python-api-comparison-resource.pages.list-python-api-comparisons';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            // Add any header widgets here if needed
        ];
    }

    public function getTitle(): string
    {
        return 'Küchenabgleich Übersicht';
    }

    protected function getFooterWidgets(): array
    {
        return [
            // Add any footer widgets here if needed
        ];
    }
}
