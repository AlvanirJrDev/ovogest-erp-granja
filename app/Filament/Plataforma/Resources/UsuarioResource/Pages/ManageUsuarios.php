<?php

namespace App\Filament\Plataforma\Resources\UsuarioResource\Pages;

use App\Filament\Plataforma\Resources\UsuarioResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageUsuarios extends ManageRecords
{
    protected static string $resource = UsuarioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
