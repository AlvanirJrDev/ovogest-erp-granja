<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VeiculoResource\Pages;
use App\Models\Veiculo;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class VeiculoResource extends Resource
{
    protected static ?string $model = Veiculo::class;

    protected static ?string $slug = 'veiculos';

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationGroup = 'Cadastros';

    protected static ?string $modelLabel = 'veículo';

    protected static ?string $pluralModelLabel = 'veículos';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('placa')
                    ->required()
                    ->maxLength(10)
                    ->placeholder('ABC1D23')
                    ->extraInputAttributes(['style' => 'text-transform: uppercase'])
                    ->dehydrateStateUsing(fn (?string $state) => strtoupper(trim($state ?? '')))
                    ->unique(
                        ignoreRecord: true,
                        modifyRuleUsing: fn ($rule) => $rule->where('granja_id', auth()->user()->granja_id),
                    ),
                Forms\Components\TextInput::make('modelo')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('capacidade_carga')
                    ->label('Capacidade de carga (bandejas)')
                    ->numeric()
                    ->required()
                    ->minValue(0),
                Forms\Components\Toggle::make('ativo')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->columns([
                Tables\Columns\TextColumn::make('placa')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('modelo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('capacidade_carga')
                    ->label('Capacidade (bandejas)')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('ativo')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('ativo'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->visible(fn ($record) => ! auth()->user()->can('update', $record)),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageVeiculos::route('/'),
        ];
    }
}
