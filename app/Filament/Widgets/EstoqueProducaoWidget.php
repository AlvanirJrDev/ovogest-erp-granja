<?php

namespace App\Filament\Widgets;

use App\Models\MovimentoEstoque;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class EstoqueProducaoWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 2;

    protected static bool $isLazy = false;

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(['admin', 'dono', 'financeiro', 'producao']) ?? false;
    }

    protected function getStats(): array
    {
        $producao = fn ($inicio) => (int) MovimentoEstoque::where('tipo', 'producao')
            ->where('data', '>=', $inicio)
            ->sum('quantidade');

        $estoqueTotal = (int) MovimentoEstoque::sum('quantidade');

        return [
            Stat::make('Produção hoje', $producao(today()).' bandeja(s)')
                ->description('Lançamentos de produção do dia')
                ->descriptionIcon('heroicon-m-plus-circle')
                ->color('success'),
            Stat::make('Produção na semana', $producao(now()->startOfWeek()).' bandeja(s)')
                ->description('Desde '.now()->startOfWeek()->format('d/m'))
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('info'),
            Stat::make('Produção no mês', $producao(now()->startOfMonth()).' bandeja(s)')
                ->description('Base para planejar as cargas')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('info'),
            Stat::make('Estoque atual', $estoqueTotal.' bandeja(s)')
                ->description($estoqueTotal < 0 ? 'Negativo: produção não lançada' : 'Disponível para carregar')
                ->descriptionIcon($estoqueTotal < 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-archive-box')
                ->color($estoqueTotal < 0 ? 'danger' : 'primary'),
        ];
    }
}
