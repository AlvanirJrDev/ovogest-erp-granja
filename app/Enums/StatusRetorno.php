<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum StatusRetorno: string implements HasColor, HasLabel
{
    case Aberto = 'aberto';
    case Fechado = 'fechado';

    public function getLabel(): string
    {
        return match ($this) {
            self::Aberto => 'Aberto',
            self::Fechado => 'Fechado',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Aberto => 'gray',
            self::Fechado => 'success',
        };
    }
}
