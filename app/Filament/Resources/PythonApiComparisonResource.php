<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PythonApiComparisonResource\Pages;
use App\Filament\Resources\PythonApiComparisonResource\RelationManagers;
use App\Models\PythonApiComparison;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PythonApiComparisonResource extends Resource
{
    protected static ?string $model = PythonApiComparison::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-magnifying-glass';
    protected static ?string $navigationGroup = 'Küchenabgleich ';
    protected static ?string $modelLabel = 'Küchenabgleich';
    protected static ?string $pluralModelLabel = 'Küchenabgleich';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                

                Tables\Columns\TextColumn::make('processed_at')
                    ->label('Verarbeitet am')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Erstellt von')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Erstellt am')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('overall_status')
                    ->options([
                        'pending' => 'Pending',
                        'matched' => 'Matched',
                        'mismatched' => 'Mismatched',
                        'needs_review' => 'Needs Review',
                    ]),
                Tables\Filters\SelectFilter::make('overall_verdict')
                    ->options([
                        'Übereinstimmung' => 'Übereinstimmung',
                        'Prüfung erforderlich' => 'Prüfung erforderlich',
                        'Keine Übereinstimmung' => 'Keine Übereinstimmung',
                    ]),
                Tables\Filters\Filter::make('has_mismatches')
                    ->label('Has Mismatches')
                    ->query(fn ($q) => $q->where('mismatches', '>', 0)->orWhere('review_required', '>', 0)),
                Tables\Filters\Filter::make('has_missing_items')
                    ->label('Has Missing Items')
                    ->query(fn ($q) => $q->where('missing_in_order', '>', 0)->orWhere('missing_in_confirmation', '>', 0)),
                Tables\Filters\Filter::make('today')
                    ->label('Processed Today')
                    ->query(fn ($q) => $q->whereDate('processed_at', today())),
                Tables\Filters\Filter::make('this_week')
                    ->label('Processed This Week')
                    ->query(fn ($q) => $q->whereBetween('processed_at', [now()->startOfWeek(), now()->endOfWeek()])),
            ])
            ->actions([
                Tables\Actions\Action::make('compare')
                    ->label('Vergleich anzeigen')
                    ->icon('heroicon-o-document-magnifying-glass')
                    ->color('info')
                    ->url(fn ($record) => static::getUrl('compare', ['record' => $record]))
                    ->visible(fn ($record) => !empty($record->full_payload)),
                Tables\Actions\DeleteAction::make()
                    ->label('Löschen'),
            ])
            ->headerActions([
                Tables\Actions\Action::make('fetch_new_results')
                    ->label('Neue Ergebnisse abrufen')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function () {
                        try {
                            // Use the same logic as the test page fetch button
                            $comparisonController = new \App\Http\Controllers\ComparisonController();
                            $result = $comparisonController->fetchApiResults();
                            $data = $result->getData(true);
                            
                            if ($data['success']) {
                                \Filament\Notifications\Notification::make()
                                    ->title('Erfolgreich abgerufen!')
                                    ->body('Neue Vergleichsergebnisse wurden von der Python API abgerufen und gespeichert.')
                                    ->success()
                                    ->send();
                                
                                // Refresh the table to show the new data
                                redirect(request()->url());
                            } elseif (isset($data['is_duplicate']) && $data['is_duplicate']) {
                                \Filament\Notifications\Notification::make()
                                    ->title('Bereits verarbeitet')
                                    ->body('Diese Ergebnisse wurden bereits verarbeitet. Keine neuen Daten verfügbar.')
                                    ->info()
                                    ->send();
                            } else {
                                \Filament\Notifications\Notification::make()
                                    ->title('Keine neuen Ergebnisse')
                                    ->body($data['message'] ?? 'Keine neuen Ergebnisse verfügbar.')
                                    ->info()
                                    ->send();
                            }
                        } catch (\Exception $e) {
                            \Filament\Notifications\Notification::make()
                                ->title('Fehler beim Abrufen')
                                ->body('Fehler: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Neue Ergebnisse abrufen')
                    ->modalDescription('Möchten Sie neue Vergleichsergebnisse direkt von der Python API abrufen?')
                    ->modalSubmitActionLabel('Abrufen')
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPythonApiComparisons::route('/'),
            'create' => Pages\CreatePythonApiComparison::route('/create'),
            'compare' => Pages\ComparePythonApiResults::route('/{record}/compare'),
        ];
    }
}
