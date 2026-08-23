<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum TipoMovimentoEstoque: string implements HasColor, HasLabel
{
    case Producao = 'producao';
    case Carga = 'carga';
    case Retorno = 'retorno';
    case Ajuste = 'ajuste';

    public function getLabel(): string
    {
        return match ($this) {
            self::Producao => 'Produção',
            self::Carga => 'Carregamento',
            self::Retorno => 'Retorno',
            self::Ajuste => 'Ajuste',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Producao => 'success',
            self::Carga => 'warning',
            self::Retorno => 'info',
            self::Ajuste => 'gray',
        };
    }
}
