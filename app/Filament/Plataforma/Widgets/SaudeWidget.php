<?php

namespace App\Filament\Plataforma\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

/** Saúde do sistema para o super admin: erros, fila e atividade. */
class SaudeWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 2;

    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        $erros24h = (int) DB::table('falhas')->where('ultima_em', '>=', now()->subDay())->sum('ocorrencias');
        $jobsFalhos = (int) DB::table('failed_jobs')->count();
        $filaPendente = (int) DB::table('jobs')->count();
        $acessosHoje = (int) DB::table('acessos')->whereDate('logado_em', today())->count();

        return [
            Stat::make('Erros (24h)', $erros24h)
                ->description($erros24h > 0 ? 'Verifique a tela Falhas' : 'Sistema saudável')
                ->descriptionIcon($erros24h > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($erros24h > 0 ? 'danger' : 'success'),
            Stat::make('E-mails/jobs falhos', $jobsFalhos)
                ->description($jobsFalhos > 0 ? 'php artisan queue:retry all' : 'Fila sem falhas')
                ->descriptionIcon('heroicon-m-envelope')
                ->color($jobsFalhos > 0 ? 'danger' : 'success'),
            Stat::make('Fila pendente', $filaPendente)
                ->description('Trabalhos aguardando o worker')
                ->descriptionIcon('heroicon-m-queue-list')
                ->color($filaPendente > 10 ? 'warning' : 'gray'),
            Stat::make('Acessos hoje', $acessosHoje)
                ->description('Logins em todas as granjas')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),
        ];
    }
}
