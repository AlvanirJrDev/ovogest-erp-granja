<?php

namespace App\Filament\Resources\ProdutoResource\Pages;

use App\Filament\Resources\ProdutoResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageProdutos extends ManageRecords
{
    protected static string $resource = ProdutoResource::class;

    public function getSubheading(): ?string
    {
        return 'Tipos de ovo por tamanho de bandeja, com preco de venda e custo — a base do calculo de margem e do estoque.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
