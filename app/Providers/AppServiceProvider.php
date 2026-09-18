<?php

namespace App\Providers;

use App\Contracts\Repositories\AccountRepositoryInterface;
use App\Contracts\Repositories\AuditRepositoryInterface;
use App\Contracts\Repositories\InvestmentRepositoryInterface;
use App\Contracts\Repositories\LimitRequestRepositoryInterface;
use App\Contracts\Repositories\MovementRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Events\AccountBlockStatusChanged;
use App\Events\UserCredentialsCreated;
use App\Listeners\LogAccountBlockStatusChange;
use App\Listeners\SendUserCredentialsEmail;
use App\Repositories\AccountRepository;
use App\Repositories\AuditRepository;
use App\Repositories\InvestmentRepository;
use App\Repositories\LimitRequestRepository;
use App\Repositories\MovementRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(AccountRepositoryInterface::class, AccountRepository::class);
        $this->app->bind(LimitRequestRepositoryInterface::class, LimitRequestRepository::class);
        $this->app->bind(MovementRepositoryInterface::class, MovementRepository::class);
        $this->app->bind(InvestmentRepositoryInterface::class, InvestmentRepository::class);
        $this->app->bind(AuditRepositoryInterface::class, AuditRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(UserCredentialsCreated::class, SendUserCredentialsEmail::class);
        Event::listen(AccountBlockStatusChanged::class, LogAccountBlockStatusChange::class);
    }
}
