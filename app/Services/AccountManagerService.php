<?php

namespace App\Services;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Enums\UserRole;
use App\Events\UserCredentialsCreated;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use InvalidArgumentException;

class AccountManagerService
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
    ) {}

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->users->paginateByRole(UserRole::AccountManager, $perPage);
    }

    /**
     * @param  array{name: string, email: string, password?: string|null}  $data
     */
    public function create(array $data): User
    {
        $plainPassword = $data['password'] ?? Str::password(12);

        $manager = DB::transaction(function () use ($data, $plainPassword): User {
            return $this->users->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($plainPassword),
                'role' => UserRole::AccountManager,
                'email_verified_at' => now(),
            ]);
        });

        UserCredentialsCreated::dispatch(
            $manager,
            $plainPassword,
            'Sua conta de Gerente de Conta foi criada no GILbank.'
        );

        return $manager;
    }

    /**
     * @param  array{name?: string, email?: string, password?: string|null}  $data
     */
    public function update(User $manager, array $data): User
    {
        if (! $manager->isAccountManager()) {
            throw new InvalidArgumentException('Somente gerentes de conta podem ser atualizados por este serviço.');
        }

        $payload = array_filter([
            'name' => $data['name'] ?? null,
            'email' => $data['email'] ?? null,
        ], fn ($value) => $value !== null);

        if (! empty($data['password'])) {
            $payload['password'] = Hash::make($data['password']);
        }

        return $this->users->update($manager, $payload);
    }

    public function delete(User $manager): void
    {
        if (! $manager->isAccountManager()) {
            throw new InvalidArgumentException('Somente gerentes de conta podem ser removidos por este serviço.');
        }

        if ($manager->managedAccounts()->exists()) {
            throw new InvalidArgumentException('Não é possível excluir um gerente que ainda possui contas vinculadas.');
        }

        $this->users->delete($manager);
    }
}
