<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser, HasTenants
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'granja_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function granja()
    {
        return $this->belongsTo(Granja::class);
    }

    /** Rótulo amigável do perfil principal, exibido na barra do painel. */
    public function getPerfilLabelAttribute(): string
    {
        return match ($this->roles->first()?->name) {
            'super_admin' => 'Super Admin',
            'dono' => 'Dono',
            'admin' => 'Administrativo',
            'financeiro' => 'Financeiro',
            'vendedor' => 'Vendedor',
            'producao' => 'Produção',
            'motorista' => 'Motorista',
            default => 'Usuário',
        };
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'plataforma') {
            return $this->hasRole('super_admin');
        }

        // Motorista e cliente terão acesso próprio em fase futura.
        return $this->granja_id !== null
            && $this->hasAnyRole(['admin', 'dono', 'vendedor', 'financeiro', 'producao']);
    }

    /** Cada usuário pertence a uma única granja (tenant). */
    public function getTenants(Panel $panel): Collection
    {
        return $this->granja ? collect([$this->granja]) : collect();
    }

    public function canAccessTenant(Model $tenant): bool
    {
        return $tenant->getKey() === $this->granja_id;
    }
}
