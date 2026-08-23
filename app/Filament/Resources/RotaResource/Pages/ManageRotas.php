<?php

namespace App\Filament\Resources\RotaResource\Pages;

use App\Filament\Resources\RotaResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageRotas extends ManageRecords
{
    protected static string $resource = RotaResource::class;

    public function getSubheading(): ?string
    {
        return 'Trajetos de entrega, com veiculo, vendedor responsavel e os clientes atendidos em cada um.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
