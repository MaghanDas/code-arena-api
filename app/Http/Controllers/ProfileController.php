<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();

        $submissions = Submission::with('challenge:id,title,difficulty')
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        $accepted = $submissions->where('status', 'accepted')->count();
        $total    = $submissions->count();

        return response()->json([
            'user'            => $user,
            'total_submissions' => $total,
            'accepted'          => $accepted,
            'acceptance_rate'   => $total > 0 ? round(($accepted / $total) * 100, 1) : 0,
            'submissions'       => $submissions,
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'name'     => 'sometimes|string|max:255',
            'password' => 'sometimes|string|min:8|confirmed',
        ]);

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $request->user()->update($data);

        return response()->json($request->user()->fresh());
    }

    public function index(Request $request)
    {
        if (! $request->user()->isAdmin()) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        return response()->json(User::latest()->get());
    }

    public function updateRole(Request $request, User $user)
    {
        if (! $request->user()->isAdmin()) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $data = $request->validate([
            'role' => 'required|in:user,admin',
        ]);

        $user->update($data);

        return response()->json($user->fresh());
    }
}