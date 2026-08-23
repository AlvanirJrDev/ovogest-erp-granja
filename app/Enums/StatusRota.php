<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum StatusRota: string implements HasColor, HasLabel
{
    case Planejada = 'planejada';
    case EmAndamento = 'em_andamento';
    case Finalizada = 'finalizada';

    public function getLabel(): string
    {
        return match ($this) {
            self::Planejada => 'Planejada',
            self::EmAndamento => 'Em andamento',
            self::Finalizada => 'Finalizada',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Planejada => 'gray',
            self::EmAndamento => 'warning',
            self::Finalizada => 'success',
        };
    }
}
