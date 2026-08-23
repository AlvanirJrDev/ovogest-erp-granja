<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Venda;
use Filament\Notifications\Notification;
use Illuminate\Console\Command;

/**
 * O sistema cobra o fiado: vendas a prazo com vencimento estourado e
 * saldo em aberto viram um resumo diário por granja para admin e
 * financeiro — uma notificação só, não um sino por venda.
 */
class AlertarVendasVencidas extends Command
{
    protected $signature = 'ovogest:alertar-vencidas';

    protected $description = 'Notifica admin e financeiro sobre vendas a prazo vencidas com saldo em aberto';

    public function handle(): int
    {
        $vencidas = Venda::withoutGlobalScopes()
            ->whereNull('cancelada_em')
            ->whereNotNull('data_vencimento')
            ->whereDate('data_vencimento', '<', today())
            ->with('cliente')
            ->get()
            ->filter(fn (Venda $venda) => $venda->valor_em_aberto > 0.009);

        foreach ($vencidas->groupBy('granja_id') as $granjaId => $vendas) {
            $responsaveis = User::role(['admin', 'financeiro'])
                ->where('granja_id', $granjaId)
                ->get();

            if ($responsaveis->isEmpty()) {
                continue;
            }

            $total = $vendas->sum(fn (Venda $venda) => $venda->valor_em_aberto);

            $exemplos = $vendas->sortBy('data_vencimento')
                ->take(3)
                ->map(fn (Venda $venda) => sprintf(
                    '#%d %s — R$ %s (venceu %s)',
                    $venda->numero,
                    $venda->cliente->nome,
                    number_format($venda->valor_em_aberto, 2, ',', '.'),
                    $venda->data_vencimento->format('d/m'),
                ))
                ->implode(' · ');

            $extras = $vendas->count() > 3 ? ' e mais '.($vendas->count() - 3).' venda(s).' : '';

            Notification::make()
                ->title(sprintf(
                    '%d venda(s) vencida(s) — R$ %s em aberto',
                    $vendas->count(),
                    number_format($total, 2, ',', '.'),
                ))
                ->body($exemplos.$extras.' Cobre o estabelecimento ou registre a baixa em Vendas → Receber.')
                ->icon('heroicon-o-exclamation-triangle')
                ->danger()
                ->sendToDatabase($responsaveis);
        }

        $this->info($vencidas->count().' venda(s) vencida(s) em '.$vencidas->groupBy('granja_id')->count().' granja(s).');

        return self::SUCCESS;
    }
}
