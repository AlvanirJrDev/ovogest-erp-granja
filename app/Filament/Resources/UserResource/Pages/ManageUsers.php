<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ManageRecords;

class ManageUsers extends ManageRecords
{
    protected static string $resource = UserResource::class;

    public function getSubheading(): ?string
    {
        return 'Acessos da equipe da granja: administrativo, financeiro, vendedor e producao — cada perfil ve apenas o que precisa.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                // Garantia explícita: usuário criado pelo dono pertence à granja da URL
                ->mutateFormDataUsing(fn (array $data): array => array_merge($data, [
                    'granja_id' => Filament::getTenant()?->getKey(),
                ])),
        ];
    }
}
