<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum StatusConciliacao: string implements HasColor, HasLabel
{
    case Ok = 'ok';
    case Divergente = 'divergente';

    public function getLabel(): string
    {
        return match ($this) {
            self::Ok => 'OK',
            self::Divergente => 'Divergente',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Ok => 'success',
            self::Divergente => 'danger',
        };
    }
}
