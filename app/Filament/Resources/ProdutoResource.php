<?php

namespace App\Filament\Resources;

use App\Enums\TipoBandeja;
use App\Filament\Resources\ProdutoResource\Pages;
use App\Models\Produto;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProdutoResource extends Resource
{
    protected static ?string $model = Produto::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationGroup = 'Cadastros';

    protected static ?string $modelLabel = 'produto';

    protected static ?string $pluralModelLabel = 'produtos';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nome')
                    ->label('Tipo de ovo')
                    ->helperText('Escolha um tipo sugerido ou digite o seu. Cada combinação de tipo + bandeja é um produto com preço próprio.')
                    ->datalist([
                        'Ovo Branco',
                        'Ovo Vermelho',
                        'Ovo Caipira',
                        'Ovo Orgânico',
                        'Ovo Galado (fértil)',
                        'Ovo de Codorna',
                        'Ovo Jumbo',
                        'Ovo Extra',
                        'Ovo Grande',
                        'Ovo Médio',
                        'Ovo Pequeno',
                    ])
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('tipo_bandeja')
                    ->label('Tipo de bandeja')
                    ->options(TipoBandeja::class)
                    ->required(),
                Forms\Components\TextInput::make('preco_venda')
                    ->label('Preço de venda (tabela)')
                    ->numeric()
                    ->prefix('R$')
                    ->required()
                    ->minValue(0),
                Forms\Components\TextInput::make('custo_unitario')
                    ->label('Custo unitário')
                    ->helperText('Usado no cálculo de margem no dashboard.')
                    ->numeric()
                    ->prefix('R$')
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
                Tables\Columns\TextColumn::make('nome')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tipo_bandeja')
                    ->label('Bandeja')
                    ->badge(),
                Tables\Columns\TextColumn::make('preco_venda')
                    ->label('Preço de venda')
                    ->money('BRL', locale: 'pt_BR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('custo_unitario')
                    ->label('Custo')
                    ->money('BRL', locale: 'pt_BR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('estoque_atual')
                    ->label('Em estoque')
                    ->suffix(' band.')
                    ->color(fn (int $state) => $state < 0 ? 'danger' : ($state === 0 ? 'gray' : 'success'))
                    ->weight('bold'),
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
            'index' => Pages\ManageProdutos::route('/'),
        ];
    }
}
