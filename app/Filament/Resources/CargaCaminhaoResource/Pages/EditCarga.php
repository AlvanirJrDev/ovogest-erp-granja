<?php

namespace App\Filament\Resources\CargaCaminhaoResource\Pages;

use App\Enums\StatusCarga;
use App\Filament\Resources\CargaCaminhaoResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditCarga extends EditRecord
{
    protected static string $resource = CargaCaminhaoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('pdf')
                ->label('Gerar PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('gray')
                ->url(fn () => route('cargas.pdf', $this->record))
                ->openUrlInNewTab(),
            Actions\Action::make('fechar')
                ->label('Fechar carga')
                ->icon('heroicon-o-lock-closed')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Fechar carga')
                ->modalDescription('Após o fechamento os itens não poderão mais ser alterados e a carga estará liberada para vendas. Confirmar?')
                ->visible(fn () => $this->record->status === StatusCarga::Aberta)
                ->action(function () {
                    try {
                        $this->record->fechar();

                        Notification::make()
                            ->title("Carga #{$this->record->numero} fechada")
                            ->success()
                            ->send();

                        $this->redirect($this->getResource()::getUrl('index'));
                    } catch (ValidationException $exception) {
                        Notification::make()
                            ->title('Não foi possível fechar a carga')
                            ->body(collect($exception->errors())->flatten()->first())
                            ->danger()
                            ->send();
                    }
                }),
            Actions\DeleteAction::make()
                ->visible(fn () => $this->record->status === StatusCarga::Aberta),
        ];
    }
}
