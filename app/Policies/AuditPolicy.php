<?php

namespace App\Policies;

use App\Models\User;
use OwenIt\Auditing\Models\Audit;

class AuditPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->isGeneralManager();
    }

    public function view(User $actor, Audit $audit): bool
    {
        return $actor->isGeneralManager();
    }
}
