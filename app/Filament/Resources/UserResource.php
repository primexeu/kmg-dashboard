<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon  = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Administration';
    protected static ?string $modelLabel      = 'User';
    protected static ?string $pluralModelLabel= 'Users';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Identity')->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->rules([
                        fn ($record) => Rule::unique('users', 'email')->ignore($record?->id),
                    ]),

                Forms\Components\Select::make('role')
                    ->label('User Role')
                    ->options([
                        'admin' => 'Administrator',
                        'manager' => 'Manager',
                        'qc_team' => 'QC Team',
                        'operator' => 'Operator',
                        'viewer' => 'Viewer',
                    ])
                    ->default('viewer')
                    ->required()
                    ->searchable()
                    ->helperText('Select the appropriate role for this user'),
            ])->columns(2),

            Forms\Components\Section::make('Credentials')->schema([
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->revealable()
                    ->minLength(8)
                    ->maxLength(255)
                    ->dehydrateStateUsing(fn ($state) => $state ? Hash::make($state) : null)
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $operation) => $operation === 'create'),

                Forms\Components\TextInput::make('password_confirmation')
                    ->password()
                    ->revealable()
                    ->same('password')
                    ->label('Confirm Password')
                    ->required(fn (string $operation) => $operation === 'create'),
            ])->columns(2),

            Forms\Components\Section::make('Verification')->schema([
                Forms\Components\Toggle::make('is_verified_proxy')
                    ->label('Email Verified')
                    ->live()
                    ->default(fn ($record) => $record?->email_verified_at !== null)
                    ->afterStateUpdated(function ($state, $set) {
                        $set('email_verified_at', $state ? now() : null);
                    }),

                Forms\Components\DateTimePicker::make('email_verified_at')
                    ->label('Verified At')
                    ->native(false)
                    ->seconds(false)
                    ->helperText('Changing this will also toggle Email Verified.'),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('email')
                    ->copyable()
                    ->copyMessage('Email copied')
                    ->copyMessageDuration(1500)
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('role')
                    ->label('Role')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'admin' => 'danger',
                        'manager' => 'warning',
                        'qc_team' => 'info',
                        'operator' => 'success',
                        'viewer' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'admin' => 'Administrator',
                        'manager' => 'Manager',
                        'qc_team' => 'QC Team',
                        'operator' => 'Operator',
                        'viewer' => 'Viewer',
                        default => 'Unknown',
                    })
                    ->sortable()
                    ->searchable(),

                Tables\Columns\IconColumn::make('email_verified_at')
                    ->label('Verified')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->label('User Role')
                    ->options([
                        'admin' => 'Administrator',
                        'manager' => 'Manager',
                        'qc_team' => 'QC Team',
                        'operator' => 'Operator',
                        'viewer' => 'Viewer',
                    ])
                    ->multiple(),

                Tables\Filters\TernaryFilter::make('verified')
                    ->label('Email Verified')
                    ->queries(
                        true: fn ($q) => $q->whereNotNull('email_verified_at'),
                        false: fn ($q) => $q->whereNull('email_verified_at'),
                        blank: fn ($q) => $q
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // Add RelationManagers later (e.g., Documents authored), if you want
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view'   => Pages\ViewUser::route('/{record}'),
            'edit'   => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
{
    return ['name', 'email', 'role'];
}

public static function getGlobalSearchResultTitle(\Illuminate\Database\Eloquent\Model $record): string
{
    return (string) $record->name;
}

public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
{
    return [
        'Email'    => $record->email,
        'Role'     => $record->getRoleDisplayName(),
        'Verified' => $record->email_verified_at ? 'Yes' : 'No',
    ];
}

public static function getGlobalSearchResultUrl(\Illuminate\Database\Eloquent\Model $record): string
{
    return static::hasPage('view')
        ? static::getUrl('view', ['record' => $record])
        : static::getUrl('edit', ['record' => $record]);
}

}
