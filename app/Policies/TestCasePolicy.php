<?php

namespace App\Policies;

use App\Models\TestCase;
use App\Models\User;
use Illuminate\Auth\Access\Response;


class TestCasePolicy
{
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, TestCase $testCase): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, TestCase $testCase): bool
    {
        return $user->isAdmin();
    }
}