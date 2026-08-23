<?php

namespace App\Filament\Resources\VendaResource\Pages;

use App\Filament\Resources\VendaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVendas extends ListRecords
{
    protected static string $resource = VendaResource::class;

    public function getSubheading(): ?string
    {
        return 'Pedidos registrados em rota, com pagamento e situacao (pago, parcial, em aberto). A nota em PDF vai por e-mail ao estabelecimento.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Nova venda'),
        ];
    }
}
