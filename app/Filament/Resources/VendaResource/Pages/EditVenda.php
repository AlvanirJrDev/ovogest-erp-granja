<?php

namespace App\Filament\Resources\VendaResource\Pages;

use App\Filament\Resources\VendaResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditVenda extends EditRecord
{
    protected static string $resource = VendaResource::class;

    /** Erros dos models (saldo, duplicidade...) viram notificação visível. */
    public function save(bool $shouldRedirect = true, bool $shouldSendSavedNotification = true): void
    {
        try {
            parent::save($shouldRedirect, $shouldSendSavedNotification);
        } catch (ValidationException $e) {
            Notification::make()
                ->title('Não foi possível salvar a venda')
                ->body(collect($e->errors())->flatten()->first())
                ->danger()
                ->persistent()
                ->send();
        }
    }
}
