<?php

namespace App\Filament\Plataforma\Widgets;

use App\Models\Granja;
use App\Models\User;
use App\Models\Venda;
use App\Models\VendaItem;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * KPIs da plataforma para o super admin: saúde do negócio SaaS,
 * não a operação de uma granja específica.
 */
class PlataformaStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        $granjasAtivas = Granja::where('ativo', true)->count();
        $granjasTotal = Granja::count();
        $usuarios = User::whereNotNull('granja_id')->count();

        $vendasMes = Venda::where('data_hora', '>=', now()->startOfMonth())->count();

        $volumeMes = (float) VendaItem::whereHas(
            'venda',
            fn ($q) => $q->where('data_hora', '>=', now()->startOfMonth()),
        )->selectRaw('coalesce(sum(quantidade * valor_unitario), 0) as total')->value('total');

        return [
            Stat::make('Granjas ativas', "{$granjasAtivas} de {$granjasTotal}")
                ->description('Clientes com operação habilitada')
                ->descriptionIcon('heroicon-m-home-modern')
                ->color('success'),
            Stat::make('Usuários nas granjas', $usuarios)
                ->description('Contas criadas pelos donos')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),
            Stat::make('Movimentação no mês', 'R$ '.number_format($volumeMes, 2, ',', '.'))
                ->description("{$vendasMes} venda(s) em todas as granjas")
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('primary'),
        ];
    }
}
