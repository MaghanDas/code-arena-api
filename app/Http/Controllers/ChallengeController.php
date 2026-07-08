<?php

namespace App\Http\Controllers;

use App\Models\Challenge;
use Illuminate\Http\Request;

class ChallengeController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user('sanctum');

        $query = Challenge::with('author:id,name');

        if (! $user || ! $user->isAdmin() || ! $request->boolean('include_unpublished')) {
            $query->where('is_published', true);
        }

        if ($request->has('difficulty')) {
            $query->where('difficulty', $request->difficulty);
        }

        return response()->json($query->latest()->get());
    }

    public function show(Challenge $challenge)
    {
        $user = auth('sanctum')->user();

        $canViewUnpublished = $user && (
            $user->isAdmin() ||
            $challenge->contests()->whereHas('participants', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->exists()
        );

        if (! $challenge->is_published && ! $canViewUnpublished) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        $challenge->load('author:id,name');

        // Only return visible test cases to non-admins
        $testCases = $user && $user->isAdmin()
            ? $challenge->testCases
            : $challenge->testCases()->where('is_hidden', false)->get();

        return response()->json([
            'challenge'  => $challenge,
            'test_cases' => $testCases,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Challenge::class);

        $data = $request->validate([
            'title'        => 'required|string|max:255',
            'description'  => 'required|string',
            'difficulty'   => 'required|in:easy,medium,hard',
            'time_limit'   => 'required|integer|min:5|max:300',
            'is_published' => 'boolean',
        ]);

        $challenge = Challenge::create([
            ...$data,
            'created_by' => $request->user()->id,
        ]);

        return response()->json($challenge, 201);
    }

    public function update(Request $request, Challenge $challenge)
    {
        $this->authorize('update', $challenge);

        $data = $request->validate([
            'title'        => 'sometimes|string|max:255',
            'description'  => 'sometimes|string',
            'difficulty'   => 'sometimes|in:easy,medium,hard',
            'time_limit'   => 'sometimes|integer|min:5|max:300',
            'is_published' => 'sometimes|boolean',
        ]);

        $challenge->update($data);

        return response()->json($challenge);
    }

    public function destroy(Challenge $challenge)
    {
        $this->authorize('delete', $challenge);
        $challenge->delete();

        return response()->json(['message' => 'Challenge deleted.']);
    }
}