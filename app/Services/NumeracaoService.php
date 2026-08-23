<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class NumeracaoService
{
    /**
     * Gera o próximo número sequencial para um tipo de nota (carga, venda,
     * retorno), independente por granja. Usa lock pessimista para garantir
     * que o número nunca se repita, mesmo com requisições concorrentes.
     * Números não são reutilizados após cancelamento.
     */
    public static function proximo(string $tipo, ?int $granjaId = null): int
    {
        $granjaId ??= 0;

        return DB::transaction(function () use ($tipo, $granjaId) {
            $sequencia = DB::table('nota_sequencias')
                ->where('tipo', $tipo)
                ->where('granja_id', $granjaId)
                ->lockForUpdate()
                ->first();

            if ($sequencia === null) {
                DB::table('nota_sequencias')->insert([
                    'tipo' => $tipo,
                    'granja_id' => $granjaId,
                    'ultimo_numero' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $sequencia = DB::table('nota_sequencias')
                    ->where('tipo', $tipo)
                    ->where('granja_id', $granjaId)
                    ->lockForUpdate()
                    ->first();
            }

            $proximo = $sequencia->ultimo_numero + 1;

            DB::table('nota_sequencias')
                ->where('tipo', $tipo)
                ->where('granja_id', $granjaId)
                ->update(['ultimo_numero' => $proximo, 'updated_at' => now()]);

            return $proximo;
        });
    }
}
