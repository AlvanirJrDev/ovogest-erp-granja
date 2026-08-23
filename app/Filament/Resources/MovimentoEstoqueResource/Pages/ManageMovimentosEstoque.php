<?php

namespace App\Filament\Resources\MovimentoEstoqueResource\Pages;

use App\Filament\Resources\MovimentoEstoqueResource;
use App\Models\MovimentoEstoque;
use App\Models\Produto;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;

class ManageMovimentosEstoque extends ManageRecords
{
    protected static string $resource = MovimentoEstoqueResource::class;

    public function getSubheading(): ?string
    {
        return 'Livro-razao das bandejas: producao entra, carregamento sai, sobras e devolucoes voltam. Saldo negativo indica producao nao lancada.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('producao')
                ->label('Lançar produção')
                ->icon('heroicon-o-plus-circle')
                ->visible(fn () => auth()->user()->hasAnyRole(['admin', 'producao']))
                ->form(MovimentoEstoqueResource::formProducao())
                ->action(function (array $data) {
                    MovimentoEstoque::create($data + ['tipo' => 'producao']);

                    Notification::make()->title('Produção lançada no estoque')->success()->send();
                }),
            Action::make('ajuste')
                ->label('Ajuste de inventário')
                ->icon('heroicon-o-adjustments-horizontal')
                ->color('gray')
                ->visible(fn () => auth()->user()->hasRole('admin'))
                ->form([
                    Forms\Components\Select::make('produto_id')
                        ->label('Produto')
                        ->options(fn () => Produto::get()->mapWithKeys(fn ($p) => [$p->id => $p->nome_completo]))
                        ->required(),
                    Forms\Components\TextInput::make('quantidade')
                        ->label('Quantidade (negativa para baixa)')
                        ->helperText('Ex.: -12 para dar baixa em 12 bandejas quebradas no galpão.')
                        ->numeric()
                        ->integer()
                        ->required(),
                    Forms\Components\TextInput::make('observacao')
                        ->label('Motivo do ajuste')
                        ->required()
                        ->maxLength(255),
                ])
                ->action(function (array $data) {
                    MovimentoEstoque::create($data + ['tipo' => 'ajuste', 'data' => today()]);

                    Notification::make()->title('Ajuste registrado')->success()->send();
                }),
        ];
    }
}
