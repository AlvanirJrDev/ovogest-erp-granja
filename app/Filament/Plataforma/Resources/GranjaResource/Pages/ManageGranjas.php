<?php

namespace App\Filament\Plataforma\Resources\GranjaResource\Pages;

use App\Filament\Plataforma\Resources\GranjaResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageGranjas extends ManageRecords
{
    protected static string $resource = GranjaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Nova granja'),
        ];
    }
}
