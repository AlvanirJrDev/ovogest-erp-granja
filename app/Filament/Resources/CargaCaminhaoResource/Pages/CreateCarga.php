<?php

namespace App\Filament\Resources\CargaCaminhaoResource\Pages;

use App\Filament\Resources\CargaCaminhaoResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCarga extends CreateRecord
{
    protected static string $resource = CargaCaminhaoResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
