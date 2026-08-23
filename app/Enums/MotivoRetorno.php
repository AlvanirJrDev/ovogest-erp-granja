<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum MotivoRetorno: string implements HasColor, HasLabel
{
    case Sobra = 'sobra';
    case Quebra = 'quebra';
    case Devolucao = 'devolucao';

    public function getLabel(): string
    {
        return match ($this) {
            self::Sobra => 'Sobra',
            self::Quebra => 'Quebra',
            self::Devolucao => 'Devolução',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Sobra => 'gray',
            self::Quebra => 'danger',
            self::Devolucao => 'warning',
        };
    }
}
