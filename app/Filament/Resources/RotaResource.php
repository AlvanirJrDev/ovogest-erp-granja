<?php

namespace App\Filament\Resources;

use App\Enums\StatusRota;
use App\Filament\Resources\RotaResource\Pages;
use App\Models\Rota;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RotaResource extends Resource
{
    protected static ?string $model = Rota::class;

    protected static ?string $navigationIcon = 'heroicon-o-map';

    protected static ?string $navigationGroup = 'Cadastros';

    protected static ?string $modelLabel = 'rota';

    protected static ?string $pluralModelLabel = 'rotas';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nome')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('veiculo_id')
                    ->label('Veículo')
                    ->relationship('veiculo', 'placa', fn ($query) => $query->where('ativo', true))
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->placa} — {$record->modelo}")
                    ->required()
                    ->preload()
                    ->searchable(),
                Forms\Components\DatePicker::make('data')
                    ->required()
                    ->default(now()),
                Forms\Components\Select::make('responsavel_id')
                    ->label('Vendedor responsável')
                    ->relationship('responsavel', 'name')
                    ->default(fn () => auth()->id())
                    ->required()
                    ->preload(),
                Forms\Components\Select::make('status')
                    ->options(StatusRota::class)
                    ->default(StatusRota::Planejada->value)
                    ->required(),
                Forms\Components\Select::make('clientes')
                    ->label('Clientes da rota')
                    ->helperText('Clientes atendidos nesta rota. As vendas da carga serão associadas a eles.')
                    ->relationship('clientes', 'nome')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->columns([
                Tables\Columns\TextColumn::make('nome')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('veiculo.placa')
                    ->label('Veículo'),
                Tables\Columns\TextColumn::make('data')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('responsavel.name')
                    ->label('Vendedor responsável'),
                Tables\Columns\TextColumn::make('clientes_count')
                    ->label('Clientes')
                    ->counts('clientes'),
                Tables\Columns\TextColumn::make('status')
                    ->badge(),
            ])
            ->defaultSort('data', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(StatusRota::class),
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
            'index' => Pages\ManageRotas::route('/'),
        ];
    }
}
