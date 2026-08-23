<?php

namespace App\Filament\Widgets;

use App\Enums\StatusCarga;
use App\Models\CargaCaminhao;
use App\Models\CargaItem;
use App\Models\RetornoItem;
use App\Models\Venda;
use App\Models\VendaItem;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Painel de campo do vendedor: o dia dele e o saldo do caminhão,
 * sem expor custos ou números de outros vendedores.
 */
class VendedorStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected static bool $isLazy = false;

    public static function canView(): bool
    {
        return auth()->user()?->hasRole('vendedor') ?? false;
    }

    protected function getStats(): array
    {
        $userId = auth()->id();

        $vendasHoje = Venda::whereNull('cancelada_em')->where('vendedor_id', $userId)
            ->whereDate('data_hora', today())
            ->count();

        $faturamentoHoje = (float) VendaItem::whereHas(
            'venda',
            fn ($q) => $q->whereNull('cancelada_em')->where('vendedor_id', $userId)->whereDate('data_hora', today()),
        )->selectRaw('coalesce(sum(quantidade * valor_unitario), 0) as total')->value('total');

        $faturamentoMes = (float) VendaItem::whereHas(
            'venda',
            fn ($q) => $q->whereNull('cancelada_em')->where('vendedor_id', $userId)->where('data_hora', '>=', now()->startOfMonth()),
        )->selectRaw('coalesce(sum(quantidade * valor_unitario), 0) as total')->value('total');

        // Saldo somado das cargas em rota sob responsabilidade do vendedor
        $cargaIds = CargaCaminhao::where('status', StatusCarga::Fechada)
            ->whereHas('rota', fn ($q) => $q->where('responsavel_id', $userId))
            ->pluck('id');

        $saldoCaminhao = (int) CargaItem::whereIn('carga_caminhao_id', $cargaIds)->sum('quantidade')
            - (int) VendaItem::whereHas('venda', fn ($q) => $q->whereNull('cancelada_em')->whereIn('carga_caminhao_id', $cargaIds))->sum('quantidade')
            - (int) RetornoItem::whereHas('retorno', fn ($q) => $q->whereIn('carga_caminhao_id', $cargaIds))->sum('quantidade');

        return [
            Stat::make('Vendas de hoje', "{$vendasHoje} venda(s)")
                ->description('R$ '.number_format($faturamentoHoje, 2, ',', '.').' faturados hoje')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
            Stat::make('Bandejas no caminhão', $saldoCaminhao)
                ->description('Saldo disponível nas suas cargas em rota')
                ->descriptionIcon('heroicon-m-truck')
                ->color($saldoCaminhao > 0 ? 'warning' : 'gray'),
            Stat::make('Faturamento no mês', 'R$ '.number_format($faturamentoMes, 2, ',', '.'))
                ->description('Suas vendas desde '.now()->startOfMonth()->format('d/m'))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('info'),
        ];
    }
}
