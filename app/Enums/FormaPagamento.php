<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum FormaPagamento: string implements HasLabel
{
    case Dinheiro = 'dinheiro';
    case Pix = 'pix';
    case Prazo = 'prazo';

    public function getLabel(): string
    {
        return match ($this) {
            self::Dinheiro => 'Dinheiro',
            self::Pix => 'Pix',
            self::Prazo => 'A prazo',
        };
    }
}
