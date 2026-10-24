<?php

namespace App\Application\Readiness;

use App\Models\Establishment;
use App\Models\User;

class EstablishmentReadiness
{
    public function isReady(?User $user): bool
    {
        return $user?->establishment_id !== null
            && Establishment::query()
                ->whereKey($user->establishment_id)
                ->where('status', 'ACTIVE')
                ->exists();
    }
}
