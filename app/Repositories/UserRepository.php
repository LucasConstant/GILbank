<?php

namespace App\Repositories;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class UserRepository implements UserRepositoryInterface
{
    public function findById(int $id): ?User
    {
        return User::query()->find($id);
    }

    public function findByEmail(string $email): ?User
    {
        return User::query()->where('email', $email)->first();
    }

    public function listByRole(UserRole $role): Collection
    {
        return User::query()
            ->where('role', $role)
            ->orderBy('name')
            ->get();
    }

    public function create(array $data): User
    {
        return User::query()->create($data);
    }

    public function update(User $user, array $data): User
    {
        $user->fill($data);
        $user->save();

        return $user->refresh();
    }

    public function delete(User $user): void
    {
        $user->delete();
    }

    public function paginateByRole(UserRole $role, int $perPage = 15): LengthAwarePaginator
    {
        return User::query()
            ->where('role', $role)
            ->orderBy('name')
            ->paginate($perPage);
    }
}
