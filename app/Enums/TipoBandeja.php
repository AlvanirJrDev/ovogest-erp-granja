<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum TipoBandeja: string implements HasLabel
{
    case Doze = '12';
    case Quinze = '15';
    case Trinta = '30';

    public function getLabel(): string
    {
        return match ($this) {
            self::Doze => 'Bandeja 12 unidades',
            self::Quinze => 'Bandeja 15 unidades',
            self::Trinta => 'Bandeja 30 unidades',
        };
    }

    public function unidades(): int
    {
        return (int) $this->value;
    }
}
