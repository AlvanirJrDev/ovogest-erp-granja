<?php

namespace App\Filament\Resources\VendaResource\Pages;

use App\Filament\Resources\VendaResource;
use App\Mail\NotaVendaMail;
use App\Models\CargaCaminhao;
use App\Models\Cliente;
use App\Models\Produto;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\ValidationException;

class CreateVenda extends CreateRecord
{
    protected static string $resource = VendaResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * Bloqueios vindos dos models (saldo, duplicidade, carga fechada...)
     * usam campos que nem sempre casam com o formulário — garante que a
     * mensagem apareça como notificação visível, nunca silenciosa.
     */
    protected function onValidationError(ValidationException $exception): void
    {
        Notification::make()
            ->title('Não foi possível registrar a venda')
            ->body(collect($exception->errors())->flatten()->first())
            ->danger()
            ->persistent()
            ->send();
    }

    protected function afterCreate(): void
    {
        $venda = $this->record->load(['cliente', 'vendedor']);
        $total = number_format($venda->valor_total, 2, ',', '.');

        // Reporta cada pedido para os admins da granja (sino de notificações)
        $admins = User::role('admin')->where('granja_id', $venda->granja_id)->get();

        if ($admins->isNotEmpty()) {
            Notification::make()
                ->title("Venda #{$venda->numero} — R$ {$total}")
                ->body(sprintf(
                    '%s vendeu para %s (%s).',
                    $venda->vendedor?->name ?? 'Sistema',
                    $venda->cliente->nome,
                    $venda->status_pagamento->getLabel(),
                ))
                ->icon('heroicon-o-banknotes')
                ->sendToDatabase($admins);
        }

        // Envia a nota em PDF para o e-mail do estabelecimento
        if (filled($venda->cliente->email)) {
            Mail::to($venda->cliente->email)->queue(new NotaVendaMail($venda));

            Notification::make()
                ->title('Nota enviada por e-mail')
                ->body("A nota #{$venda->numero} foi enviada para {$venda->cliente->email}.")
                ->success()
                ->send();
        }
    }

    /** Erros dos models (saldo, duplicidade...) viram notificação visível. */
    public function create(bool $another = false): void
    {
        try {
            parent::create($another);
        } catch (ValidationException $e) {
            Notification::make()
                ->title('Não foi possível registrar a venda')
                ->body(collect($e->errors())->flatten()->first())
                ->danger()
                ->persistent()
                ->send();
        }
    }

    /** Revisão antes de finalizar: resumo da venda com opção de voltar e editar. */
    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()
            ->label('Revisar e confirmar')
            ->submit(null)
            ->action(fn () => $this->create())
            ->requiresConfirmation()
            ->modalHeading('Confira a venda antes de finalizar')
            ->modalDescription(fn () => new HtmlString($this->resumoVenda()))
            ->modalSubmitActionLabel('Confirmar venda')
            ->modalCancelActionLabel('Voltar e editar')
            ->modalWidth('lg');
    }

    private function resumoVenda(): string
    {
        $d = $this->data ?? [];
        $cliente = Cliente::find($d['cliente_id'] ?? null)?->nome ?? '—';
        $carga = CargaCaminhao::find($d['carga_caminhao_id'] ?? null);
        $formas = ['dinheiro' => 'Dinheiro', 'pix' => 'Pix', 'prazo' => 'A prazo'];

        $linhas = '';
        $total = 0.0;
        foreach (($d['itens'] ?? []) as $item) {
            $p = Produto::find($item['produto_id'] ?? null);
            $qtd = (int) ($item['quantidade'] ?? 0);
            $vu = (float) ($item['valor_unitario'] ?? 0);
            $sub = $qtd * $vu;
            $total += $sub;
            $linhas .= '<tr><td style="padding:3px 8px 3px 0;">'.e($p?->nome_completo ?? '—').'</td>'
                .'<td style="padding:3px 8px; text-align:right;">'.$qtd.' × R$ '.number_format($vu, 2, ',', '.').'</td>'
                .'<td style="padding:3px 0; text-align:right; font-weight:600;">R$ '.number_format($sub, 2, ',', '.').'</td></tr>';
        }

        $pago = (float) ($d['valor_pago'] ?? 0);
        $aberto = max($total - $pago, 0);

        return '<div style="text-align:left; font-size:13px;">'
            .'<p><b>Estabelecimento:</b> '.e($cliente).'<br>'
            .'<b>Carga:</b> '.($carga ? '#'.$carga->numero.' — '.e($carga->rota->nome) : '—').'<br>'
            .'<b>Pagamento:</b> '.($formas[$d['forma_pagamento'] ?? ''] ?? '—').'</p>'
            .'<table style="width:100%; border-collapse:collapse; margin:8px 0;">'.$linhas.'</table>'
            .'<hr style="opacity:.3; margin:8px 0;">'
            .'<p style="font-size:14px;"><b>Total: R$ '.number_format($total, 2, ',', '.').'</b>'
            .' · Pago no ato: R$ '.number_format($pago, 2, ',', '.')
            .' · <b style="color:'.($aberto > 0 ? '#dc2626' : '#16a34a').';">Em aberto: R$ '.number_format($aberto, 2, ',', '.').'</b></p>'
            .'</div>';
    }

    /** Apenas revisar+confirmar e cancelar — sem atalho que fure a revisão. */
    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction(),
            $this->getCancelFormAction(),
        ];
    }
}
