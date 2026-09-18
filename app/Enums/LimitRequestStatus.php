<?php

namespace App\Enums;

enum LimitRequestStatus: string
{
    case Pending = 'pendente';
    case Approved = 'aprovada';
    case Rejected = 'reprovada';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pendente',
            self::Approved => 'Aprovada',
            self::Rejected => 'Reprovada',
        };
    }
}
