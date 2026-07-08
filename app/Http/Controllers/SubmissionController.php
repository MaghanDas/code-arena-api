<?php

namespace App\Http\Controllers;

use App\Models\Challenge;
use App\Models\Submission;
use App\Models\ContestParticipant;
use Illuminate\Http\Request;

class SubmissionController extends Controller
{
    public function index(Request $request)
    {
        $submissions = Submission::with('challenge:id,title,difficulty')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json($submissions);
    }

    public function show(Request $request, Submission $submission)
    {
        $this->authorize('view', $submission);

        $submission->load('challenge:id,title', 'user:id,name');

        return response()->json($submission);
    }

    // public function store(Request $request, Challenge $challenge)
    // {
    //     $data = $request->validate([
    //         'code'     => 'required|string',
    //         'language' => 'required|in:javascript,python,php',
    //     ]);

    //     // Fetch ALL test cases (including hidden) for judging
    //     $testCases = $challenge->testCases;

    //     if ($testCases->isEmpty()) {
    //         return response()->json(['message' => 'No test cases found for this challenge.'], 422);
    //     }

    //     $startTime = microtime(true);
    //     $allPassed = true;
    //     $results   = [];

    //     foreach ($testCases as $tc) {
    //         // ── Verdict engine ─────────────────────────────────────
    //         // We compare the user's submitted expected_output against
    //         // the stored expected_output for each test case.
    //         // In a real judge you'd execute the code — here we ask
    //         // the user to submit their expected output per test case
    //         // via a JSON body key: test_outputs (array).
    //         // For now: match against submitted outputs if provided.
    //         $userOutput = $request->input("test_outputs.{$tc->id}", '');
    //         $passed = trim($userOutput) === trim($tc->expected_output);

    //         if (! $passed) {
    //             $allPassed = false;
    //         }

    //         $results[] = [
    //             'test_case_id' => $tc->id,
    //             'passed'       => $passed,
    //             'is_hidden'    => $tc->is_hidden,
    //             'input'        => $tc->is_hidden ? null : $tc->input,
    //             'expected'     => $tc->is_hidden ? null : $tc->expected_output,
    //             'got'          => $tc->is_hidden ? null : $userOutput,
    //         ];
    //     }

    //     $runtimeMs = (int) ((microtime(true) - $startTime) * 1000);
    //     $status    = $allPassed ? 'accepted' : 'wrong_answer';
    //     $score     = $allPassed ? 100 : 0;

    //     $submission = Submission::create([
    //         'user_id'      => $request->user()->id,
    //         'challenge_id' => $challenge->id,
    //         'code'         => $data['code'],
    //         'language'     => $data['language'],
    //         'status'       => $status,
    //         'score'        => $score,
    //         'runtime_ms'   => $runtimeMs,
    //     ]);

    //     // Update user's total score if accepted
    //     if ($allPassed) {
    //         $request->user()->increment('score', 100);

    //         // Update contest participant score if in active contest
    //         $activeParticipation = ContestParticipant::whereHas('contest', function ($q) {
    //             $q->where('starts_at', '<=', now())
    //               ->where('ends_at', '>=', now());
    //         })->where('user_id', $request->user()->id)->first();

    //         if ($activeParticipation) {
    //             $activeParticipation->increment('total_score', 100);
    //         }
    //     }

    //     return response()->json([
    //         'submission' => $submission,
    //         'status'     => $status,
    //         'score'      => $score,
    //         'runtime_ms' => $runtimeMs,
    //         'results'    => $results,
    //     ], 201);
    // }
public function store(Request $request, Challenge $challenge)
{
    $data = $request->validate([
        'code'     => 'required|string',
        'language' => 'required|in:javascript,python,php',
        'contest_id' => 'nullable|exists:contests,id',
    ]);

    $contestId = $data['contest_id'] ?? null;

    if ($contestId) {
        $isChallengeInContest = $challenge->contests()->where('contests.id', $contestId)->exists();
        if (! $isChallengeInContest) {
            return response()->json(['message' => 'Challenge is not part of this contest.'], 422);
        }

        $joinedContest = ContestParticipant::where('contest_id', $contestId)
            ->where('user_id', $request->user()->id)
            ->first();

        if (! $joinedContest) {
            return response()->json(['message' => 'Join the contest before submitting.'], 403);
        }
    }

    $alreadyAccepted = Submission::where('user_id', $request->user()->id)
        ->where('challenge_id', $challenge->id)
        ->when($contestId, fn ($q) => $q->where('contest_id', $contestId), fn ($q) => $q->whereNull('contest_id'))
        ->where('status', 'accepted')
        ->exists();

    $testCases = $challenge->testCases;

    if ($testCases->isEmpty()) {
        return response()->json(['message' => 'No test cases for this challenge.'], 422);
    }

    $executor  = new \App\Services\CodeExecutor();
    $allPassed = true;
    $results   = [];
    $totalMs   = 0;
    $errorMsg  = null;

    foreach ($testCases as $tc) {
        $result = $executor->run(
            $data['code'],
            $data['language'],
            $tc->input,
            $challenge->time_limit
        );

        $totalMs += $result['runtime_ms'];

        // TLE — stop immediately
        if ($result['status'] === 'tle') {
            $allPassed = false;
            $results[] = [
                'test_case_id' => $tc->id,
                'passed'       => false,
                'is_hidden'    => $tc->is_hidden,
                'input'        => $tc->is_hidden ? null : $tc->input,
                'expected'     => $tc->is_hidden ? null : $tc->expected_output,
                'got'          => 'Time limit exceeded',
                'error'        => null,
            ];
            break;
        }

        // Runtime error — stop immediately
        if ($result['status'] === 'error') {
            $allPassed = false;
            $errorMsg  = $result['error'];
            $results[] = [
                'test_case_id' => $tc->id,
                'passed'       => false,
                'is_hidden'    => $tc->is_hidden,
                'input'        => $tc->is_hidden ? null : $tc->input,
                'expected'     => $tc->is_hidden ? null : $tc->expected_output,
                'got'          => null,
                'error'        => $result['error'],
            ];
            break;
        }

        // Compare output (trim to ignore trailing newlines)
        $passed = trim($result['output']) === trim($tc->expected_output);

        if (!$passed) $allPassed = false;

        $results[] = [
            'test_case_id' => $tc->id,
            'passed'       => $passed,
            'is_hidden'    => $tc->is_hidden,
            'input'        => $tc->is_hidden ? null : $tc->input,
            'expected'     => $tc->is_hidden ? null : $tc->expected_output,
            'got'          => $tc->is_hidden ? null : $result['output'],
            'error'        => null,
        ];
    }

    $status = $allPassed ? 'accepted' : (
        isset($result) && $result['status'] === 'tle' ? 'tle' : 'wrong_answer'
    );
    $score  = $allPassed ? 100 : 0;

    $submission = Submission::create([
        'user_id'      => $request->user()->id,
        'challenge_id' => $challenge->id,
        'contest_id'   => $contestId,
        'code'         => $data['code'],
        'language'     => $data['language'],
        'status'       => $status,
        'score'        => $score,
        'runtime_ms'   => $totalMs,
    ]);

    if ($allPassed && ! $alreadyAccepted) {
        if ($contestId) {
            ContestParticipant::where('contest_id', $contestId)
                ->where('user_id', $request->user()->id)
                ->increment('total_score', 100);
        } else {
            $request->user()->increment('score', 100);
        }
    }

    return response()->json([
        'submission' => $submission,
        'status'     => $status,
        'score'      => $score,
        'runtime_ms' => $totalMs,
        'error'      => $errorMsg,
        'results'    => $results,
    ], 201);
}
    public function destroy(Request $request, Submission $submission)
    {
        $this->authorize('delete', $submission);
        $submission->delete();

        return response()->json(['message' => 'Submission deleted.']);
    }
}