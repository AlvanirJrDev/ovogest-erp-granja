<?php

namespace App\Filament\Resources\VeiculoResource\Pages;

use App\Filament\Resources\VeiculoResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageVeiculos extends ManageRecords
{
    protected static string $resource = VeiculoResource::class;

    public function getSubheading(): ?string
    {
        return 'Caminhoes e utilitarios da granja, com capacidade de carga em bandejas.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
