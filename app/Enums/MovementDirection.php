<?php

namespace App\Enums;

enum MovementDirection: string
{
    case Inbound = 'entrada';
    case Outbound = 'saida';

    public function label(): string
    {
        return match ($this) {
            self::Inbound => 'Entrada',
            self::Outbound => 'Saída',
        };
    }

    public function isPositive(): bool
    {
        return $this === self::Inbound;
    }
}
