<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum StatusCarga: string implements HasColor, HasLabel
{
    case Aberta = 'aberta';
    case Fechada = 'fechada';
    case Conciliada = 'conciliada';

    public function getLabel(): string
    {
        return match ($this) {
            self::Aberta => 'Aberta (carregando)',
            self::Fechada => 'Fechada (em rota)',
            self::Conciliada => 'Conciliada',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Aberta => 'gray',
            self::Fechada => 'warning',
            self::Conciliada => 'success',
        };
    }
}
