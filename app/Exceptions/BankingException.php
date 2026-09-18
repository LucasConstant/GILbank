<?php

namespace App\Exceptions;

use RuntimeException;

class BankingException extends RuntimeException
{
    public static function insufficientFunds(): self
    {
        return new self('Saldo insuficiente considerando o limite disponível.');
    }

    public static function accountBlocked(): self
    {
        return new self('A conta está bloqueada e não pode realizar esta operação.');
    }

    public static function destinationNotFound(): self
    {
        return new self('Conta de destino não encontrada.');
    }

    public static function sameAccount(): self
    {
        return new self('Não é possível transferir para a mesma conta.');
    }

    public static function invalidAmount(): self
    {
        return new self('O valor informado deve ser maior que zero.');
    }

    public static function investmentNotFound(): self
    {
        return new self('Aplicação não encontrada para o tipo informado.');
    }

    public static function insufficientInvestment(): self
    {
        return new self('Saldo aplicado insuficiente para o resgate.');
    }

    public static function limitRequestNotPending(): self
    {
        return new self('A solicitação de limite não está pendente.');
    }

    public static function accountNotFound(): self
    {
        return new self('Conta não encontrada.');
    }
}
