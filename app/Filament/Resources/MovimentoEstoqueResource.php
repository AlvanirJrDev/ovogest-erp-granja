<?php

namespace App\Filament\Resources;

use App\Enums\TipoMovimentoEstoque;
use App\Filament\Resources\MovimentoEstoqueResource\Pages;
use App\Models\MovimentoEstoque;
use App\Models\Produto;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MovimentoEstoqueResource extends Resource
{
    protected static ?string $model = MovimentoEstoque::class;

    protected static ?string $slug = 'estoque';

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $navigationGroup = 'Operação';

    protected static ?int $navigationSort = 5;

    protected static ?string $modelLabel = 'movimento de estoque';

    protected static ?string $pluralModelLabel = 'estoque';

    protected static ?string $navigationLabel = 'Estoque';

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->columns([
                Tables\Columns\TextColumn::make('data')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('produto.nome_completo')
                    ->label('Produto'),
                Tables\Columns\TextColumn::make('tipo')
                    ->badge(),
                Tables\Columns\TextColumn::make('quantidade')
                    ->label('Bandejas')
                    ->formatStateUsing(fn (int $state) => ($state > 0 ? '+' : '').$state)
                    ->color(fn (int $state) => $state >= 0 ? 'success' : 'danger')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('usuario.name')
                    ->label('Lançado por')
                    ->placeholder('Sistema'),
                Tables\Columns\TextColumn::make('observacao')
                    ->label('Observação')
                    ->limit(40)
                    ->placeholder('—'),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('tipo')
                    ->options(TipoMovimentoEstoque::class),
                Tables\Filters\SelectFilter::make('produto_id')
                    ->label('Produto')
                    ->options(fn () => Produto::get()->mapWithKeys(fn (Produto $p) => [$p->id => $p->nome_completo])),
            ])
            ->actions([
                // Só lançamentos manuais podem ser desfeitos (excluir e refazer)
                Tables\Actions\DeleteAction::make()
                    ->visible(
                        fn (MovimentoEstoque $record) => auth()->user()->hasRole('admin')
                            && in_array($record->tipo, [TipoMovimentoEstoque::Producao, TipoMovimentoEstoque::Ajuste], true)
                    ),
            ]);
    }

    public static function formProducao(): array
    {
        return [
            Forms\Components\Select::make('produto_id')
                ->label('Produto')
                ->options(fn () => Produto::where('ativo', true)->get()->mapWithKeys(fn (Produto $p) => [$p->id => $p->nome_completo]))
                ->required(),
            Forms\Components\TextInput::make('quantidade')
                ->label('Bandejas produzidas')
                ->numeric()
                ->integer()
                ->required()
                ->minValue(1),
            Forms\Components\DatePicker::make('data')
                ->default(today())
                ->required(),
            Forms\Components\TextInput::make('observacao')
                ->label('Observação')
                ->maxLength(255),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageMovimentosEstoque::route('/'),
        ];
    }
}
