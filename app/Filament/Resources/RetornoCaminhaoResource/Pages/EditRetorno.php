<?php

namespace App\Filament\Resources\RetornoCaminhaoResource\Pages;

use App\Enums\StatusRetorno;
use App\Filament\Resources\RetornoCaminhaoResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditRetorno extends EditRecord
{
    protected static string $resource = RetornoCaminhaoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('fechar')
                ->label('Fechar retorno')
                ->icon('heroicon-o-lock-closed')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Fechar retorno')
                ->modalDescription('Ao fechar, a nota de entrada fica imutável e a conciliação da carga é calculada automaticamente. Confirmar?')
                ->visible(fn () => $this->record->status === StatusRetorno::Aberto)
                ->action(function () {
                    try {
                        $this->record->fechar();

                        Notification::make()
                            ->title("Retorno #{$this->record->numero} fechado e conciliação calculada")
                            ->success()
                            ->send();

                        $this->redirect($this->getResource()::getUrl('index'));
                    } catch (ValidationException $exception) {
                        Notification::make()
                            ->title('Não foi possível fechar o retorno')
                            ->body(collect($exception->errors())->flatten()->first())
                            ->danger()
                            ->send();
                    }
                }),
            Actions\DeleteAction::make()
                ->visible(fn () => $this->record->status === StatusRetorno::Aberto),
        ];
    }

    /** Erros dos models (saldo, duplicidade...) viram notificação visível. */
    public function save(bool $shouldRedirect = true, bool $shouldSendSavedNotification = true): void
    {
        try {
            parent::save($shouldRedirect, $shouldSendSavedNotification);
        } catch (ValidationException $e) {
            Notification::make()
                ->title('Não foi possível salvar o retorno')
                ->body(collect($e->errors())->flatten()->first())
                ->danger()
                ->persistent()
                ->send();
        }
    }
}
