<?php

namespace App\Models;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

/** Registro deduplicado de erros para o monitoramento da plataforma. */
class Falha extends Model
{
    protected $table = 'falhas';

    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return ['primeira_em' => 'datetime', 'ultima_em' => 'datetime'];
    }

    /** Captura um erro real (ignora validação/4xx) sem nunca quebrar a request. */
    public static function capturar(Throwable $e): void
    {
        if ($e instanceof ValidationException
            || ($e instanceof HttpException && $e->getStatusCode() < 500)
            || $e instanceof AuthenticationException
            || $e instanceof AuthorizationException) {
            return;
        }

        try {
            $arquivo = str_replace(base_path().'/', '', $e->getFile()).':'.$e->getLine();
            $hash = sha1(get_class($e).'|'.$arquivo);

            $atualizadas = DB::table('falhas')->where('hash', $hash)->update([
                'mensagem' => (string) str($e->getMessage())->limit(500),
                'url' => (string) str(request()?->fullUrl() ?? 'cli')->limit(255),
                'user_id' => auth()->id(),
                'ocorrencias' => DB::raw('ocorrencias + 1'),
                'ultima_em' => now(),
            ]);

            if ($atualizadas === 0) {
                DB::table('falhas')->insert([
                    'hash' => $hash,
                    'excecao' => class_basename($e),
                    'mensagem' => (string) str($e->getMessage())->limit(500),
                    'arquivo' => $arquivo,
                    'url' => (string) str(request()?->fullUrl() ?? 'cli')->limit(255),
                    'user_id' => auth()->id(),
                    'ocorrencias' => 1,
                    'primeira_em' => now(),
                    'ultima_em' => now(),
                ]);
            }
        } catch (Throwable) {
            // monitoramento jamais derruba a aplicação
        }
    }
}
