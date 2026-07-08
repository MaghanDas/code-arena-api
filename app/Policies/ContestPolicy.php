<?php

namespace App\Policies;

use App\Models\Contest;
use App\Models\User;
use Illuminate\Auth\Access\Response;


class ContestPolicy
{
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Contest $contest): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Contest $contest): bool
    {
        return $user->isAdmin();
    }
}