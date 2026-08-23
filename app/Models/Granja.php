<?php

namespace App\Models;

use App\Models\Concerns\Auditavel;
use Filament\Models\Contracts\HasAvatar;
use Filament\Models\Contracts\HasName;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Granja extends Model implements HasAvatar, HasName
{
    use Auditavel, HasFactory;

    protected $table = 'granjas';

    protected $fillable = [
        'nome',
        'slug',
        'razao_social',
        'documento',
        'email',
        'telefone',
        'endereco',
        'logo_path',
        'ativo',
    ];

    protected function casts(): array
    {
        return [
            'ativo' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Granja $granja) {
            if (blank($granja->slug)) {
                $slug = Str::slug($granja->nome);

                while (static::where('slug', $slug)->whereKeyNot($granja->getKey())->exists()) {
                    $slug = Str::slug($granja->nome).'-'.Str::lower(Str::random(4));
                }

                $granja->slug = $slug;
            }
        });
    }

    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class);
    }

    // Relações usadas pela multi-tenancy do Filament (nomes derivados dos models)
    public function cargaCaminhaos(): HasMany
    {
        return $this->hasMany(CargaCaminhao::class);
    }

    public function vendas(): HasMany
    {
        return $this->hasMany(Venda::class);
    }

    public function retornoCaminhaos(): HasMany
    {
        return $this->hasMany(RetornoCaminhao::class);
    }

    public function produtos(): HasMany
    {
        return $this->hasMany(Produto::class);
    }

    public function veiculos(): HasMany
    {
        return $this->hasMany(Veiculo::class);
    }

    public function clientes(): HasMany
    {
        return $this->hasMany(Cliente::class);
    }

    public function rotas(): HasMany
    {
        return $this->hasMany(Rota::class);
    }

    public function movimentoEstoques(): HasMany
    {
        return $this->hasMany(MovimentoEstoque::class);
    }

    public function recebimentos(): HasMany
    {
        return $this->hasMany(Recebimento::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function auditorias(): HasMany
    {
        return $this->hasMany(Auditoria::class);
    }

    public function getFilamentName(): string
    {
        return $this->nome;
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->logo_path ? Storage::disk('public')->url($this->logo_path) : null;
    }
}
