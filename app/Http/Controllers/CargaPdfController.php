<?php

namespace App\Http\Controllers;

use App\Models\CargaCaminhao;
use Barryvdh\DomPDF\Facade\Pdf;

class CargaPdfController extends Controller
{
    public function __invoke(CargaCaminhao $carga)
    {
        $user = auth()->user();

        abort_unless(
            $user?->hasRole('admin')
                || ($user?->hasRole('vendedor') && $carga->rota->responsavel_id === $user->id),
            403,
        );

        $carga->load(['granja', 'rota.veiculo', 'rota.responsavel', 'rota.clientes', 'itens.produto']);

        $pdf = Pdf::loadView('pdf.carga', ['carga' => $carga])
            ->setPaper('a4');

        return $pdf->stream('nota-saida-'.str_pad($carga->numero, 6, '0', STR_PAD_LEFT).'.pdf');
    }
}
