<?php

namespace App\Policies;

use App\Domains\Auth\User\User;
use App\Domains\Tables\RestaurantTable;

class RestaurantTablePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->isOperationalRole($user);
    }

    public function view(User $user, RestaurantTable $table): bool
    {
        return $this->isOperationalRole($user);
    }

    public function create(User $user): bool
    {
        return $this->isOperationalRole($user);
    }

    public function update(User $user, ?RestaurantTable $table = null): bool
    {
        return $this->isOperationalRole($user);
    }

    private function isOperationalRole(User $user): bool
    {
        return in_array(strtoupper((string) $user->role), ['ADMIN', 'MANAGER', 'OPERATOR'], true);
    }
}
