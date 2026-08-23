<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\DB;

/**
 * Trilha de auditoria: registra quem criou, alterou ou excluiu, com o
 * antes/depois dos campos. Nunca interrompe a operação que está auditando.
 */
trait Auditavel
{
    protected static array $auditoriaIgnorar = ['password', 'remember_token', 'created_at', 'updated_at'];

    protected static function bootAuditavel(): void
    {
        static::created(fn ($m) => $m->registrarAuditoria('criou'));
        static::updated(function ($m) {
            $mudancas = collect($m->getChanges())
                ->except(static::$auditoriaIgnorar)
                ->mapWithKeys(fn ($novo, $campo) => [$campo => [
                    'de' => static::valorAuditavel($m->getOriginal($campo)),
                    'para' => static::valorAuditavel($novo),
                ]]);

            if ($mudancas->isNotEmpty()) {
                $m->registrarAuditoria('atualizou', $mudancas->all());
            }
        });
        static::deleted(fn ($m) => $m->registrarAuditoria('excluiu'));
    }

    private static function valorAuditavel(mixed $v): mixed
    {
        return $v instanceof \BackedEnum ? $v->value : (is_scalar($v) || $v === null ? $v : (string) $v);
    }

    public function registrarAuditoria(string $acao, ?array $alteracoes = null): void
    {
        try {
            DB::table('auditoria')->insert([
                'granja_id' => $this->granja_id ?? auth()->user()?->granja_id,
                'user_id' => auth()->id(),
                'acao' => $acao,
                'modelo' => class_basename($this),
                'registro_id' => $this->getKey() ?? 0,
                'registro_rotulo' => str($this->nome ?? $this->name ?? ($this->numero ? '#'.$this->numero : null) ?? '')->limit(80),
                'alteracoes' => $alteracoes ? json_encode($alteracoes, JSON_UNESCAPED_UNICODE) : null,
                'created_at' => now(),
            ]);
        } catch (\Throwable) {
            // auditoria jamais derruba a operação
        }
    }
}
