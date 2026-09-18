<?php

namespace App\Enums;

enum MovementType: string
{
    case PixSent = 'pix_enviado';
    case PixReceived = 'pix_recebido';
    case Application = 'aplicacao';
    case Redemption = 'resgate';

    public function label(): string
    {
        return match ($this) {
            self::PixSent => 'Pix enviado',
            self::PixReceived => 'Pix recebido',
            self::Application => 'Aplicação',
            self::Redemption => 'Resgate',
        };
    }
}
