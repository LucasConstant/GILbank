<?php

namespace App\Enums;

enum InvestmentType: string
{
    case Cdb = 'cdb';
    case Cdi = 'cdi';
    case Savings = 'poupanca';

    public function label(): string
    {
        return match ($this) {
            self::Cdb => 'CDB',
            self::Cdi => 'CDI',
            self::Savings => 'Poupança',
        };
    }
}
