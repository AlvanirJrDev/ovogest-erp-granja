<?php

namespace App\Services;

use App\Enums\StatusConciliacao;
use App\Models\CargaCaminhao;
use App\Models\Conciliacao;
use App\Models\RetornoItem;
use App\Models\VendaItem;

class ConciliacaoService
{
    /**
     * Regra de ouro do sistema: saiu = vendido + retornou (+ tolerância configurável).
     * Totais são sempre recalculados a partir dos itens — nunca editados manualmente.
     */
    public static function calcular(CargaCaminhao $carga): Conciliacao
    {
        $totalSaiu = (int) $carga->itens()->sum('quantidade');

        $totalVendido = (int) VendaItem::whereHas(
            'venda',
            fn ($query) => $query->where('carga_caminhao_id', $carga->id)->whereNull('cancelada_em')
        )->sum('quantidade');

        $totalRetornou = (int) RetornoItem::whereHas(
            'retorno',
            fn ($query) => $query->where('carga_caminhao_id', $carga->id)
        )->sum('quantidade');

        $diferenca = $totalSaiu - $totalVendido - $totalRetornou;

        $status = abs($diferenca) <= config('granja.tolerancia_conciliacao')
            ? StatusConciliacao::Ok
            : StatusConciliacao::Divergente;

        return Conciliacao::updateOrCreate(
            ['carga_caminhao_id' => $carga->id],
            [
                'granja_id' => $carga->granja_id,
                'total_saiu' => $totalSaiu,
                'total_vendido' => $totalVendido,
                'total_retornou' => $totalRetornou,
                'diferenca' => $diferenca,
                'status' => $status,
            ],
        );
    }
}
