<?php

namespace App\Events;

use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AccountBlockStatusChanged
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Account $account,
        public bool $blocked,
        public User $actor,
    ) {}
}
