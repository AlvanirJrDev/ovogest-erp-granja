<?php

namespace App\Filament\Widgets;

use App\Models\CargaItem;
use App\Models\RetornoItem;
use App\Models\Rota;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class RankingRotasQuebraWidget extends TableWidget
{
    protected static ?int $sort = 3;

    protected static bool $isLazy = false;

    protected static ?string $heading = 'Rotas por quebra';

    protected int|string|array $columnSpan = ['default' => 'full', 'lg' => 6];

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(['admin', 'dono', 'financeiro']) ?? false;
    }

    public function table(Table $table): Table
    {
        $totalSaiu = CargaItem::selectRaw('coalesce(sum(carga_itens.quantidade), 0)')
            ->join('cargas_caminhao', 'cargas_caminhao.id', '=', 'carga_itens.carga_caminhao_id')
            ->whereColumn('cargas_caminhao.rota_id', 'rotas.id');

        $totalQuebra = RetornoItem::selectRaw('coalesce(sum(retorno_itens.quantidade), 0)')
            ->join('retornos_caminhao', 'retornos_caminhao.id', '=', 'retorno_itens.retorno_caminhao_id')
            ->join('cargas_caminhao', 'cargas_caminhao.id', '=', 'retornos_caminhao.carga_caminhao_id')
            ->where('retorno_itens.motivo', 'quebra')
            ->whereColumn('cargas_caminhao.rota_id', 'rotas.id');

        return $table
            ->query(
                Rota::query()->addSelect([
                    'total_saiu' => $totalSaiu,
                    'total_quebra' => $totalQuebra,
                ])
            )
            ->columns([
                Tables\Columns\TextColumn::make('nome')
                    ->label('Rota'),
                Tables\Columns\TextColumn::make('veiculo.placa')
                    ->label('Veículo'),
                Tables\Columns\TextColumn::make('total_saiu')
                    ->label('Bandejas carregadas')
                    ->numeric(),
                Tables\Columns\TextColumn::make('total_quebra')
                    ->label('Quebra')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('percentual_quebra')
                    ->label('% quebra')
                    ->state(function (Rota $record): string {
                        if ((int) $record->total_saiu === 0) {
                            return '—';
                        }

                        return round($record->total_quebra / $record->total_saiu * 100, 1).'%';
                    })
                    ->color(
                        fn (Rota $record) => (int) $record->total_saiu > 0
                            && $record->total_quebra / $record->total_saiu > 0.02
                                ? 'danger'
                                : 'success'
                    ),
            ])
            ->defaultSort('total_quebra', 'desc')
            ->paginated(false);
    }
}
