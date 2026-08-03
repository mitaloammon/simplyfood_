<?php

namespace App\Policies;

use App\Domains\Auth\User\User;
use App\Domains\Recipe\Recipe;

class RecipePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->isOperationalRole($user);
    }

    public function view(User $user, Recipe $recipe): bool
    {
        return $this->isOperationalRole($user);
    }

    public function create(User $user): bool
    {
        return $this->isOperationalRole($user);
    }

    public function update(User $user, ?Recipe $recipe = null): bool
    {
        return $this->isOperationalRole($user);
    }

    private function isOperationalRole(User $user): bool
    {
        return in_array(strtoupper((string) $user->role), ['ADMIN', 'MANAGER', 'OPERATOR'], true);
    }
}
