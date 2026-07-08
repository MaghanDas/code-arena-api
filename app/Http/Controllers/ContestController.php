<?php

namespace App\Http\Controllers;

use App\Models\Challenge;
use App\Models\Contest;
use App\Models\ContestParticipant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ContestController extends Controller
{
    public function index()
    {
        $contests = Contest::with('author:id,name')
            ->withCount('participants')
            ->latest()
            ->get();

        return response()->json($contests);
    }

    public function show(Contest $contest)
    {
        $contest->load([
            'author:id,name',
            'challenges:id,title,difficulty,is_published',
        ]);

        return response()->json($contest);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Contest::class);

        $data = $request->validate([
            'title'                       => 'required|string|max:255',
            'description'                 => 'nullable|string',
            'starts_at'                   => 'required|date|after:now',
            'ends_at'                     => 'required|date|after:starts_at',
            'challenge_ids'               => 'sometimes|array',
            'challenge_ids.*'             => 'exists:challenges,id',
            'challenges'                  => 'sometimes|array|min:1',
            'challenges.*.title'          => 'required|string|max:255',
            'challenges.*.description'    => 'required|string',
            'challenges.*.difficulty'     => 'required|in:easy,medium,hard',
            'challenges.*.time_limit'     => 'required|integer|min:5|max:300',
            'challenges.*.is_published'   => 'boolean',
            'challenges.*.test_cases'     => 'required|array|min:1',
            'challenges.*.test_cases.*.input'           => 'required|string',
            'challenges.*.test_cases.*.expected_output'  => 'required|string',
            'challenges.*.test_cases.*.is_hidden'        => 'boolean',
        ]);

        $contest = DB::transaction(function () use ($request, $data) {
            $challengeIds = $data['challenge_ids'] ?? [];

            if (! empty($data['challenges'])) {
                foreach ($data['challenges'] as $challengeData) {
                    $challenge = Challenge::create([
                        'title'        => $challengeData['title'],
                        'description'  => $challengeData['description'],
                        'difficulty'   => $challengeData['difficulty'],
                        'time_limit'   => $challengeData['time_limit'],
                        'created_by'   => $request->user()->id,
                        'is_published' => $challengeData['is_published'] ?? false,
                    ]);

                    $challenge->testCases()->createMany(array_map(
                        fn ($testCase) => [
                            'input'           => $testCase['input'],
                            'expected_output' => $testCase['expected_output'],
                            'is_hidden'       => $testCase['is_hidden'] ?? false,
                        ],
                        $challengeData['test_cases']
                    ));

                    $challengeIds[] = $challenge->id;
                }
            }

            $challengeIds = array_values(array_unique($challengeIds));

            if (empty($challengeIds)) {
                throw ValidationException::withMessages([
                    'challenges' => ['Add at least one challenge.'],
                ]);
            }

            $contest = Contest::create([
                'title'       => $data['title'],
                'description' => $data['description'] ?? null,
                'starts_at'   => $data['starts_at'],
                'ends_at'     => $data['ends_at'],
                'created_by'  => $request->user()->id,
            ]);

            $contest->challenges()->attach($challengeIds);

            return $contest->load('challenges:id,title,difficulty,is_published');
        });

        return response()->json($contest, 201);
    }

    public function update(Request $request, Contest $contest)
    {
        $this->authorize('update', $contest);

        $data = $request->validate([
            'title'           => 'sometimes|string|max:255',
            'description'     => 'sometimes|nullable|string',
            'starts_at'       => 'sometimes|date',
            'ends_at'         => 'sometimes|date|after:starts_at',
            'challenge_ids'   => 'sometimes|array',
            'challenge_ids.*' => 'exists:challenges,id',
        ]);

        $contest->update(collect($data)->except('challenge_ids')->toArray());

        if (isset($data['challenge_ids'])) {
            $contest->challenges()->sync($data['challenge_ids']);
        }

        return response()->json($contest->load('challenges:id,title'));
    }

    public function destroy(Contest $contest)
    {
        $this->authorize('delete', $contest);
        $contest->delete();

        return response()->json(['message' => 'Contest deleted.']);
    }

    public function join(Request $request, Contest $contest)
    {
        $already = ContestParticipant::where('contest_id', $contest->id)
            ->where('user_id', $request->user()->id)
            ->exists();

        if ($already) {
            return response()->json(['message' => 'Already joined.'], 409);
        }

        ContestParticipant::create([
            'contest_id'  => $contest->id,
            'user_id'     => $request->user()->id,
            'total_score' => 0,
        ]);

        return response()->json(['message' => 'Joined contest successfully.'], 201);
    }

    public function leaderboard(Contest $contest)
    {
        $participants = ContestParticipant::with('user:id,name')
            ->where('contest_id', $contest->id)
            ->orderByDesc('total_score')
            ->get()
            ->map(function ($p, $index) {
                return [
                    'rank'        => $index + 1,
                    'user'        => $p->user,
                    'total_score' => $p->total_score,
                    'joined_at'   => $p->joined_at,
                ];
            });

        return response()->json($participants);
    }
}