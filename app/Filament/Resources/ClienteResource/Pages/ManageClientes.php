<?php

namespace App\Filament\Resources\ClienteResource\Pages;

use App\Filament\Resources\ClienteResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageClientes extends ManageRecords
{
    protected static string $resource = ClienteResource::class;

    public function getSubheading(): ?string
    {
        return 'Estabelecimentos que compram da granja. O e-mail cadastrado recebe a nota de venda automaticamente.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
