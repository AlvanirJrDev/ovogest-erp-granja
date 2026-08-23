<?php

namespace App\Filament\Resources\ConciliacaoResource\Pages;

use App\Filament\Resources\ConciliacaoResource;
use Filament\Resources\Pages\ListRecords;

class ListConciliacoes extends ListRecords
{
    protected static string $resource = ConciliacaoResource::class;

    public function getSubheading(): ?string
    {
        return 'A prova real de cada carga: saida = vendas + retorno. Qualquer diferenca aparece aqui como divergente.';
    }
}
