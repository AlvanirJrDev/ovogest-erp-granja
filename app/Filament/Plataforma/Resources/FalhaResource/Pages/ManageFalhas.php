<?php

namespace App\Filament\Plataforma\Resources\FalhaResource\Pages;

use App\Filament\Plataforma\Resources\FalhaResource;
use Filament\Resources\Pages\ManageRecords;

class ManageFalhas extends ManageRecords
{
    protected static string $resource = FalhaResource::class;

    public function getSubheading(): ?string
    {
        return 'Erros reais do sistema, deduplicados. Excluir uma falha significa marcá-la como resolvida — se voltar, reaparece.';
    }
}
