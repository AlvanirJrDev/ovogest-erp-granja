<?php

namespace App\Console\Commands;

use App\Enums\StatusCarga;
use App\Models\CargaCaminhao;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Console\Command;

/**
 * Rotina real: a carga é montada num dia para rodar no outro. Se passar
 * de 24h fechada sem retorno registrado, o admin da granja é avisado —
 * o sistema cobra a conciliação em vez de forçá-la (fechar sem conferir
 * quebraria a equação saída = vendas + retorno).
 */
class AlertarRetornosPendentes extends Command
{
    protected $signature = 'ovogest:alertar-retornos';

    protected $description = 'Notifica os admins sobre cargas fechadas há mais de 24h sem retorno';

    public function handle(): int
    {
        $pendentes = CargaCaminhao::withoutGlobalScopes()
            ->where('status', StatusCarga::Fechada)
            ->where('data_hora_saida', '<=', now()->subHours(24))
            ->whereDoesntHave('retorno', fn ($q) => $q->where('status', 'fechado'))
            ->with('rota')
            ->get();

        foreach ($pendentes as $carga) {
            $admins = User::role('admin')->where('granja_id', $carga->granja_id)->get();

            if ($admins->isEmpty()) {
                continue;
            }

            $horas = (int) $carga->data_hora_saida->diffInHours(now());

            Notification::make()
                ->title("Carga #{$carga->numero} sem retorno há {$horas}h")
                ->body("A carga da rota \"{$carga->rota->nome}\" saiu em {$carga->data_hora_saida->format('d/m H:i')} e ainda não teve o retorno registrado. Registre o retorno para conciliar e encerrar a rota.")
                ->icon('heroicon-o-clock')
                ->warning()
                ->sendToDatabase($admins);
        }

        $this->info($pendentes->count().' carga(s) pendente(s) notificada(s).');

        return self::SUCCESS;
    }
}
