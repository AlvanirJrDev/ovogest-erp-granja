<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum StatusPagamento: string implements HasColor, HasLabel
{
    case Pago = 'pago';
    case Parcial = 'parcial';
    case EmAberto = 'em_aberto';
    case Cancelada = 'cancelada';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pago => 'Pago',
            self::Parcial => 'Parcial',
            self::EmAberto => 'Em aberto',
            self::Cancelada => 'Cancelada',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Pago => 'success',
            self::Parcial => 'warning',
            self::EmAberto => 'danger',
            self::Cancelada => 'gray',
        };
    }
}
