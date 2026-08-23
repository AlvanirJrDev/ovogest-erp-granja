<?php

namespace App\Filament\Resources\CargaCaminhaoResource\Pages;

use App\Filament\Resources\CargaCaminhaoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCargas extends ListRecords
{
    protected static string $resource = CargaCaminhaoResource::class;

    public function getSubheading(): ?string
    {
        return 'Notas de saida: o que subiu em cada caminhao. Fechar a carga trava os itens, baixa o estoque e libera as vendas da rota.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Nova carga'),
        ];
    }
}
