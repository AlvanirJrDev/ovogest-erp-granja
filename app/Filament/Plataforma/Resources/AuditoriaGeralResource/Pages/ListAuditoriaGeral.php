<?php

namespace App\Filament\Plataforma\Resources\AuditoriaGeralResource\Pages;

use App\Filament\Plataforma\Resources\AuditoriaGeralResource;
use Filament\Resources\Pages\ListRecords;

class ListAuditoriaGeral extends ListRecords
{
    protected static string $resource = AuditoriaGeralResource::class;

    public function getSubheading(): ?string
    {
        return 'Acoes de todas as granjas, filtraveis por granja — o super admin ve tudo, as granjas veem apenas o proprio rastro.';
    }
}
