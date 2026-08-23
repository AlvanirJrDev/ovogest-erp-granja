<?php

namespace App\Filament\Widgets;

use App\Models\Cliente;
use App\Models\VendaItem;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class RankingClientesWidget extends TableWidget
{
    protected static ?int $sort = 4;

    protected static bool $isLazy = false;

    protected static ?string $heading = 'Clientes por volume de compra';

    protected int|string|array $columnSpan = ['default' => 'full', 'lg' => 6];

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(['admin', 'dono', 'financeiro']) ?? false;
    }

    public function table(Table $table): Table
    {
        $totalFaturado = VendaItem::selectRaw('coalesce(sum(venda_itens.quantidade * venda_itens.valor_unitario), 0)')
            ->join('vendas', 'vendas.id', '=', 'venda_itens.venda_id')
            ->whereNull('vendas.cancelada_em')
            ->whereColumn('vendas.cliente_id', 'clientes.id');

        $totalBandejas = VendaItem::selectRaw('coalesce(sum(venda_itens.quantidade), 0)')
            ->join('vendas', 'vendas.id', '=', 'venda_itens.venda_id')
            ->whereNull('vendas.cancelada_em')
            ->whereColumn('vendas.cliente_id', 'clientes.id');

        return $table
            ->query(
                Cliente::query()
                    ->addSelect([
                        'total_faturado' => $totalFaturado,
                        'total_bandejas' => $totalBandejas,
                    ])
                    ->withCount(['vendas' => fn ($q) => $q->whereNull('cancelada_em')])
            )
            ->columns([
                Tables\Columns\TextColumn::make('nome')
                    ->label('Cliente'),
                Tables\Columns\TextColumn::make('vendas_count')
                    ->label('Compras')
                    ->numeric(),
                Tables\Columns\TextColumn::make('total_bandejas')
                    ->label('Bandejas')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_faturado')
                    ->label('Faturamento')
                    ->money('BRL', locale: 'pt_BR')
                    ->sortable(),
            ])
            ->defaultSort('total_faturado', 'desc')
            ->paginated(false);
    }
}
