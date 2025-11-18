<?php

namespace App\Filament\Resources\PythonApiComparisonResource\Pages;

use App\Filament\Resources\PythonApiComparisonResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPythonApiComparison extends EditRecord
{
    protected static string $resource = PythonApiComparisonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
