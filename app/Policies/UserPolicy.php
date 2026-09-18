<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->isGeneralManager();
    }

    public function view(User $actor, User $user): bool
    {
        if ($actor->isGeneralManager()) {
            return $user->isAccountManager();
        }

        return $actor->id === $user->id;
    }

    public function create(User $actor): bool
    {
        return $actor->isGeneralManager();
    }

    public function update(User $actor, User $user): bool
    {
        return $actor->isGeneralManager() && $user->isAccountManager();
    }

    public function delete(User $actor, User $user): bool
    {
        return $actor->isGeneralManager()
            && $user->isAccountManager()
            && $actor->id !== $user->id;
    }
}
