<?php

namespace App\Policies;

use App\Models\Account;
use App\Models\User;

class AccountPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->isAccountManager() || $actor->isGeneralManager();
    }

    public function view(User $actor, Account $account): bool
    {
        if ($actor->isGeneralManager()) {
            return true;
        }

        if ($actor->isAccountManager()) {
            return $account->gerente_id === $actor->id;
        }

        return $actor->isCustomer() && $account->user_id === $actor->id;
    }

    public function create(User $actor): bool
    {
        return $actor->isAccountManager();
    }

    public function update(User $actor, Account $account): bool
    {
        return $actor->isAccountManager() && $account->gerente_id === $actor->id;
    }

    public function delete(User $actor, Account $account): bool
    {
        return $actor->isAccountManager() && $account->gerente_id === $actor->id;
    }

    public function block(User $actor, Account $account): bool
    {
        return $actor->isAccountManager() && $account->gerente_id === $actor->id;
    }

    public function unblock(User $actor, Account $account): bool
    {
        return $this->block($actor, $account);
    }

    public function viewStatement(User $actor, Account $account): bool
    {
        if ($actor->isAccountManager() && $account->gerente_id === $actor->id) {
            return true;
        }

        if ($actor->isCustomer() && $account->user_id === $actor->id) {
            return ! $account->isBlocked();
        }

        return false;
    }

    public function transfer(User $actor, Account $account): bool
    {
        return $actor->isCustomer()
            && $account->user_id === $actor->id
            && ! $account->isBlocked();
    }

    public function invest(User $actor, Account $account): bool
    {
        return $this->transfer($actor, $account);
    }

    public function redeem(User $actor, Account $account): bool
    {
        return $this->transfer($actor, $account);
    }
}
