<?php

namespace App\Filament\Widgets;

use App\Models\CargaItem;
use App\Models\Recebimento;
use App\Models\RetornoItem;
use App\Models\Venda;
use App\Models\VendaItem;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class FinanceiroStatsWidget extends StatsOverviewWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 1;

    protected static bool $isLazy = false;

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(['admin', 'dono', 'financeiro']) ?? false;
    }

    protected function getStats(): array
    {
        /** @var array{0: ?Carbon, 1: string} $periodo */
        $periodo = match ($this->filters['periodo'] ?? 'mes') {
            'hoje' => [now()->startOfDay(), 'hoje'],
            '7dias' => [now()->subDays(6)->startOfDay(), 'nos últimos 7 dias'],
            'tudo' => [null, 'desde o início'],
            default => [now()->startOfMonth(), 'no mês'],
        };

        [$inicio, $rotulo] = $periodo;

        $faturamento = (float) VendaItem::query()
            ->whereHas('venda', fn ($query) => $query->whereNull('cancelada_em')->when($inicio, fn ($q) => $q->where('data_hora', '>=', $inicio)))
            ->selectRaw('coalesce(sum(quantidade * valor_unitario), 0) as total')
            ->value('total');

        $custo = (float) VendaItem::query()
            ->join('produtos', 'produtos.id', '=', 'venda_itens.produto_id')
            ->whereHas('venda', fn ($query) => $query->whereNull('cancelada_em')->when($inicio, fn ($q) => $q->where('data_hora', '>=', $inicio)))
            ->selectRaw('coalesce(sum(venda_itens.quantidade * produtos.custo_unitario), 0) as total')
            ->value('total');

        $margem = $faturamento - $custo;

        $quebra = (int) RetornoItem::query()
            ->where('motivo', 'quebra')
            ->whereHas('retorno', fn ($query) => $query->when($inicio, fn ($q) => $q->where('data_hora_retorno', '>=', $inicio)))
            ->sum('quantidade');

        $totalSaiu = (int) CargaItem::query()
            ->whereHas('carga', fn ($query) => $query->when($inicio, fn ($q) => $q->where('data_hora_saida', '>=', $inicio)))
            ->sum('quantidade');

        $percentualQuebra = $totalSaiu > 0 ? round($quebra / $totalSaiu * 100, 1) : 0.0;

        // A receber é sempre acumulado — dívida não expira com o período
        $totalVendidoGeral = (float) VendaItem::query()
            ->whereHas('venda', fn ($q) => $q->whereNull('cancelada_em'))
            ->selectRaw('coalesce(sum(quantidade * valor_unitario), 0) as total')
            ->value('total');

        $totalRecebido = (float) Venda::whereNull('cancelada_em')->sum('valor_pago')
            + (float) Recebimento::whereHas('venda', fn ($q) => $q->whereNull('cancelada_em'))->sum('valor');

        $aReceber = max($totalVendidoGeral - $totalRecebido, 0);

        // Sparkline: faturamento dos últimos 7 dias
        $serieSemanal = collect(range(6, 0))
            ->map(fn (int $diasAtras) => (float) VendaItem::whereHas(
                'venda',
                fn ($q) => $q->whereNull('cancelada_em')->whereDate('data_hora', today()->subDays($diasAtras)),
            )->selectRaw('coalesce(sum(quantidade * valor_unitario), 0) as total')->value('total'))
            ->all();

        return [
            Stat::make('Faturamento', self::reais($faturamento))
                ->description('Total vendido '.$rotulo)
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart($serieSemanal)
                ->color('success'),
            Stat::make('Margem', self::reais($margem))
                ->description('Faturamento menos custo '.$rotulo)
                ->descriptionIcon('heroicon-m-banknotes')
                ->color($margem >= 0 ? 'success' : 'danger'),
            Stat::make('Quebra', "{$quebra} bandeja(s) ({$percentualQuebra}%)")
                ->description('Sobre o carregado '.$rotulo)
                ->descriptionIcon($percentualQuebra > 2 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($percentualQuebra > 2 ? 'danger' : 'success'),
            Stat::make('A receber', self::reais($aReceber))
                ->description('Acumulado de vendas a prazo/parciais')
                ->descriptionIcon('heroicon-m-clock')
                ->color($aReceber > 0 ? 'warning' : 'success'),
        ];
    }

    private static function reais(float $valor): string
    {
        return 'R$ '.number_format($valor, 2, ',', '.');
    }
}
