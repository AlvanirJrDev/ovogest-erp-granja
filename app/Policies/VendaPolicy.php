<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Venda;

/**
 * Admin tem controle total. Vendedor registra vendas e enxerga
 * apenas as próprias — sem editar nem excluir depois de criadas
 * (auditoria: ajustes são feitos pelo admin).
 */
class VendaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'dono', 'financeiro', 'vendedor']);
    }

    public function view(User $user, Venda $venda): bool
    {
        return $user->hasAnyRole(['admin', 'dono', 'financeiro'])
            || ($user->hasRole('vendedor') && $venda->vendedor_id === $user->id);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'vendedor']);
    }

    public function update(User $user, Venda $venda): bool
    {
        // Financeiro registra baixas de pagamento
        return $user->hasAnyRole(['admin', 'financeiro']);
    }

    public function delete(User $user, Venda $venda): bool
    {
        return $user->hasRole('admin');
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasRole('admin');
    }
}
