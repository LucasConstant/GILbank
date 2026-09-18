<?php

namespace App\Policies;

use App\Models\LimitRequest;
use App\Models\User;

class LimitRequestPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->isGeneralManager() || $actor->isAccountManager();
    }

    public function view(User $actor, LimitRequest $limitRequest): bool
    {
        if ($actor->isGeneralManager()) {
            return true;
        }

        return $actor->isAccountManager() && $limitRequest->gerente_id === $actor->id;
    }

    public function create(User $actor): bool
    {
        return $actor->isAccountManager();
    }

    public function review(User $actor, LimitRequest $limitRequest): bool
    {
        return $actor->isGeneralManager() && $limitRequest->isPending();
    }

    public function approve(User $actor, LimitRequest $limitRequest): bool
    {
        return $this->review($actor, $limitRequest);
    }

    public function reject(User $actor, LimitRequest $limitRequest): bool
    {
        return $this->review($actor, $limitRequest);
    }
}
