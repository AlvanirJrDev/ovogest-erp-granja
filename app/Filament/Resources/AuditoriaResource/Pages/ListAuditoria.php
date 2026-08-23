<?php

namespace App\Filament\Resources\AuditoriaResource\Pages;

use App\Filament\Resources\AuditoriaResource;
use Filament\Resources\Pages\ListRecords;

class ListAuditoria extends ListRecords
{
    protected static string $resource = AuditoriaResource::class;

    public function getSubheading(): ?string
    {
        return 'Tudo que foi criado, alterado ou excluido na granja — por quem, quando e com o antes/depois de cada campo.';
    }
}
