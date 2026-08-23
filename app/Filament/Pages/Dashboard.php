<?php

namespace App\Filament\Pages;

use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    protected static ?string $navigationLabel = 'Visão geral';

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    public function getHeading(): string
    {
        $usuario = auth()->user();
        $primeiroNome = str($usuario->name)->before(' ');

        return "Olá, {$primeiroNome}";
    }

    public function getSubheading(): ?string
    {
        $granja = Filament::getTenant();

        return $granja?->nome.' · '.now()->translatedFormat('l, d \d\e F \d\e Y');
    }

    public function getColumns(): int|string|array
    {
        return 12;
    }

    public function getTitle(): string
    {
        return 'Visão geral';
    }

    public function filtersForm(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('periodo')
                    ->label('Período dos indicadores')
                    ->options([
                        'hoje' => 'Hoje',
                        '7dias' => 'Últimos 7 dias',
                        'mes' => 'Este mês',
                        'tudo' => 'Desde o início',
                    ])
                    ->default('mes')
                    ->selectablePlaceholder(false)
                    ->live(),
            ])
            ->columns(3);
    }
}
