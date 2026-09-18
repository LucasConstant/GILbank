<?php

namespace App\Listeners;

use App\Events\AccountBlockStatusChanged;
use Illuminate\Support\Facades\Log;

class LogAccountBlockStatusChange
{
    public function handle(AccountBlockStatusChanged $event): void
    {
        Log::info('Account block status changed', [
            'account_id' => $event->account->id,
            'blocked' => $event->blocked,
            'actor_id' => $event->actor->id,
        ]);
    }
}
