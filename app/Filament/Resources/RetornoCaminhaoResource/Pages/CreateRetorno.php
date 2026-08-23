<?php

namespace App\Filament\Resources\RetornoCaminhaoResource\Pages;

use App\Filament\Resources\RetornoCaminhaoResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreateRetorno extends CreateRecord
{
    protected static string $resource = RetornoCaminhaoResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /** Erros dos models (saldo, duplicidade...) viram notificação visível. */
    public function create(bool $another = false): void
    {
        try {
            parent::create($another);
        } catch (ValidationException $e) {
            Notification::make()
                ->title('Não foi possível registrar o retorno')
                ->body(collect($e->errors())->flatten()->first())
                ->danger()
                ->persistent()
                ->send();
        }
    }
}
