<?php

namespace App\Http\Controllers;

use App\Models\Venda;
use Barryvdh\DomPDF\Facade\Pdf;

class VendaPdfController extends Controller
{
    public function __invoke(Venda $venda)
    {
        $user = auth()->user();

        abort_unless(
            $user?->hasAnyRole(['admin', 'financeiro'])
                || ($user?->hasRole('vendedor') && $venda->vendedor_id === $user->id),
            403,
        );

        // Nota cancelada não é emitida — sem marcação de cancelamento,
        // o PDF pareceria um documento válido.
        abort_if($venda->cancelada_em !== null, 410, 'Esta venda foi cancelada.');

        $venda->load(['cliente', 'vendedor', 'carga.rota', 'itens.produto']);

        $pdf = Pdf::loadView('pdf.venda', ['venda' => $venda])->setPaper('a4');

        return $pdf->stream('nota-venda-'.str_pad($venda->numero, 6, '0', STR_PAD_LEFT).'.pdf');
    }
}
