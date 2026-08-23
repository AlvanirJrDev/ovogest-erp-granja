<?php

namespace App\Models;

use App\Models\Concerns\Auditavel;
use App\Models\Concerns\PertenceAGranja;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Cliente extends Model
{
    use Auditavel, HasFactory, PertenceAGranja;

    protected $table = 'clientes';

    protected $fillable = [
        'granja_id',
        'nome',
        'documento',
        'email',
        'telefone',
        'endereco',
        'tabela_preco_id',
    ];

    public function vendas(): HasMany
    {
        return $this->hasMany(Venda::class);
    }

    public function itensVendidos(): HasManyThrough
    {
        return $this->hasManyThrough(VendaItem::class, Venda::class);
    }

    public function rotas(): BelongsToMany
    {
        return $this->belongsToMany(Rota::class, 'cliente_rota')->withTimestamps();
    }
}
