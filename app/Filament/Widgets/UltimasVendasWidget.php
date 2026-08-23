<?php

namespace App\Filament\Widgets;

use App\Models\Venda;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class UltimasVendasWidget extends TableWidget
{
    protected static ?int $sort = 5;

    protected static bool $isLazy = false;

    protected static ?string $heading = 'Últimas vendas';

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(['admin', 'dono', 'financeiro', 'vendedor']) ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Venda::query()
                    ->with(['cliente', 'vendedor', 'carga'])
                    ->when(
                        auth()->user()?->hasRole('vendedor'),
                        fn ($query) => $query->where('vendedor_id', auth()->id()),
                    )
                    ->latest('data_hora')
                    ->limit(8)
            )
            ->columns([
                Tables\Columns\TextColumn::make('numero')
                    ->label('Nº')
                    ->prefix('#'),
                Tables\Columns\TextColumn::make('data_hora')
                    ->label('Data/hora')
                    ->dateTime('d/m/Y H:i'),
                Tables\Columns\TextColumn::make('cliente.nome')
                    ->label('Estabelecimento'),
                Tables\Columns\TextColumn::make('vendedor.name')
                    ->label('Vendedor'),
                Tables\Columns\TextColumn::make('carga.numero')
                    ->label('Carga')
                    ->prefix('#'),
                Tables\Columns\TextColumn::make('valor_total')
                    ->label('Total')
                    ->money('BRL', locale: 'pt_BR'),
                Tables\Columns\TextColumn::make('status_pagamento')
                    ->label('Situação')
                    ->badge(),
            ])
            ->paginated(false);
    }
}
