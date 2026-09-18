<?php

namespace App\Services;

use App\Contracts\Repositories\AccountRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Enums\UserRole;
use App\Exceptions\BankingException;
use App\Models\Account;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class CustomerAuthService
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly AccountRepositoryInterface $accounts,
    ) {}

    /**
     * @return array{user: User, account: Account, token: string}
     */
    public function login(string $email, string $password): array
    {
        $user = $this->users->findByEmail($email);

        if ($user === null || $user->role !== UserRole::Customer || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['As credenciais informadas estão incorretas.'],
            ]);
        }

        $account = $this->accounts->findByCustomerId($user->id);

        if ($account === null) {
            throw BankingException::accountNotFound();
        }

        $token = $user->createToken('customer-spa')->plainTextToken;

        return [
            'user' => $user,
            'account' => $account,
            'token' => $token,
        ];
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }

    public function accountFor(User $user): Account
    {
        $account = $this->accounts->findByCustomerId($user->id);

        if ($account === null) {
            throw BankingException::accountNotFound();
        }

        return $account;
    }
}
