<?php

namespace App\Enums;

enum UserRole: string
{
    case GeneralManager = 'gerente_geral';
    case AccountManager = 'gerente_conta';
    case Customer = 'cliente';

    public function label(): string
    {
        return match ($this) {
            self::GeneralManager => 'Gerente Geral',
            self::AccountManager => 'Gerente de Conta',
            self::Customer => 'Cliente',
        };
    }

    public function isStaff(): bool
    {
        return $this === self::GeneralManager || $this === self::AccountManager;
    }
}
