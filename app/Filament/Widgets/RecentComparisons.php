<?php

namespace App\Filament\Widgets;

use App\Models\PythonApiComparison;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentComparisons extends BaseWidget
{
    protected static ?int $sort = 20;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                PythonApiComparison::query()
                    ->latest('processed_at')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->sortable(),
                    
                
                    
                Tables\Columns\TextColumn::make('processed_at')
                    ->label('Verarbeitet am')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Erstellt von')
                    ->limit(20),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('Anzeigen')
                    ->icon('heroicon-o-eye')
                    ->url(fn (PythonApiComparison $record): string => 
                        \App\Filament\Resources\PythonApiComparisonResource::getUrl('compare', ['record' => $record])
                    ),
            ])
            ->heading('Letzte Küchenabgleiche')
            ->description('Die 10 zuletzt verarbeiteten Küchenabgleiche');
    }
}
