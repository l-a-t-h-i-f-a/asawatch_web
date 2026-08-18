<?php

namespace App\Policies;

use App\Models\Sesi;
use App\Models\User;

class SesiPolicy
{
    public function view(User $user, Sesi $sesi): bool
    {
        return $user->id === $sesi->user_id;
    }

    public function update(User $user, Sesi $sesi): bool
    {
        return $user->id === $sesi->user_id;
    }

    public function delete(User $user, Sesi $sesi): bool
    {
        return $user->id === $sesi->user_id;
    }
}
