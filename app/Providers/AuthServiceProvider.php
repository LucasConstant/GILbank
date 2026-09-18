<?php

namespace App\Providers;

use App\Models\Account;
use App\Models\LimitRequest;
use App\Models\User;
use App\Policies\AccountPolicy;
use App\Policies\AuditPolicy;
use App\Policies\LimitRequestPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use OwenIt\Auditing\Models\Audit;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class => UserPolicy::class,
        Account::class => AccountPolicy::class,
        LimitRequest::class => LimitRequestPolicy::class,
        Audit::class => AuditPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('access-general-manager-area', function (User $user): bool {
            return $user->isGeneralManager();
        });

        Gate::define('access-account-manager-area', function (User $user): bool {
            return $user->isAccountManager();
        });

        Gate::define('access-staff-area', function (User $user): bool {
            return $user->role->isStaff();
        });
    }
}
