<?php

namespace App\Mail;

use App\Models\Venda;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Attachment;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotaVendaMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Venda $venda) {}

    public function envelope(): Envelope
    {
        $numero = str_pad($this->venda->numero, 6, '0', STR_PAD_LEFT);

        return new Envelope(
            subject: "OvoGest — Nota de venda Nº {$numero}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.nota-venda',
            with: ['venda' => $this->venda],
        );
    }

    public function attachments(): array
    {
        $numero = str_pad($this->venda->numero, 6, '0', STR_PAD_LEFT);

        return [
            Attachment::fromData(
                fn () => Pdf::loadView('pdf.venda', [
                    'venda' => $this->venda->load(['cliente', 'vendedor', 'carga.rota', 'itens.produto']),
                ])->setPaper('a4')->output(),
                "nota-venda-{$numero}.pdf",
            )->withMime('application/pdf'),
        ];
    }
}
