<?php

namespace App\Filament\Resources\RetornoCaminhaoResource\Pages;

use App\Filament\Resources\RetornoCaminhaoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRetornos extends ListRecords
{
    protected static string $resource = RetornoCaminhaoResource::class;

    public function getSubheading(): ?string
    {
        return 'Notas de entrada: o que voltou de cada rota, separado por sobra, quebra e devolucao. Fechar o retorno calcula a conciliacao.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Novo retorno'),
        ];
    }
}
