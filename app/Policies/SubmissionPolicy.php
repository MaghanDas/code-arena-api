<?php

namespace App\Policies;

use App\Models\Submission;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SubmissionPolicy
{
    public function view(User $user, Submission $submission): bool
    {
        return $user->id === $submission->user_id || $user->isAdmin();
    }

    public function delete(User $user, Submission $submission): bool
    {
        return $user->isAdmin();
    }
}