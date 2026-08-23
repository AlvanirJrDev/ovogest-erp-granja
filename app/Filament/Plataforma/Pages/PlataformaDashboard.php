<?php

namespace App\Filament\Plataforma\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class PlataformaDashboard extends BaseDashboard
{
    protected static ?string $navigationLabel = 'Visão geral';

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    public function getHeading(): string
    {
        return 'Plataforma OvoGest';
    }

    public function getSubheading(): ?string
    {
        return 'Visão consolidada das granjas clientes · '.now()->translatedFormat('d \d\e F \d\e Y');
    }

    public function getTitle(): string
    {
        return 'Visão geral';
    }
}
