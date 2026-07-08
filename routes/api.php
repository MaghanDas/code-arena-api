<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChallengeController;
use App\Http\Controllers\TestCaseController;
use App\Http\Controllers\SubmissionController;
use App\Http\Controllers\ContestController;
use App\Http\Controllers\ProfileController;

// ── Public auth routes ──────────────────────────────────────────
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

// ── Public read routes ──────────────────────────────────────────
Route::get('/challenges',              [ChallengeController::class, 'index']);
Route::get('/challenges/{challenge}',  [ChallengeController::class, 'show']);
Route::get('/contests',                [ContestController::class, 'index']);
Route::get('/contests/{contest}',      [ContestController::class, 'show']);
Route::get('/contests/{contest}/leaderboard', [ContestController::class, 'leaderboard']);

// ── Authenticated routes ────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user',    function () {
        return response()->json(auth()->user());
    });

    // Profile
    Route::get('/profile',   [ProfileController::class, 'show']);
    Route::patch('/profile', [ProfileController::class, 'update']);

    // Challenges (write)
    Route::post('/challenges',             [ChallengeController::class, 'store']);
    Route::patch('/challenges/{challenge}',[ChallengeController::class, 'update']);
    Route::delete('/challenges/{challenge}',[ChallengeController::class, 'destroy']);

    // Test cases
    Route::get('/challenges/{challenge}/test-cases',   [TestCaseController::class, 'index']);
    Route::post('/challenges/{challenge}/test-cases',  [TestCaseController::class, 'store']);
    Route::patch('/test-cases/{testCase}',             [TestCaseController::class, 'update']);
    Route::delete('/test-cases/{testCase}',            [TestCaseController::class, 'destroy']);

    // Submissions
    Route::post('/challenges/{challenge}/submissions', [SubmissionController::class, 'store']);
    Route::get('/submissions',                         [SubmissionController::class, 'index']);
    Route::get('/submissions/{submission}',            [SubmissionController::class, 'show']);
    Route::delete('/submissions/{submission}',         [SubmissionController::class, 'destroy']);

    // Contests (write)
    Route::post('/contests',              [ContestController::class, 'store']);
    Route::patch('/contests/{contest}',   [ContestController::class, 'update']);
    Route::delete('/contests/{contest}',  [ContestController::class, 'destroy']);
    Route::post('/contests/{contest}/join', [ContestController::class, 'join']);

    // Admin: user management
    Route::get('/users',                  [ProfileController::class, 'index']);
    Route::patch('/users/{user}/role',    [ProfileController::class, 'updateRole']);
});