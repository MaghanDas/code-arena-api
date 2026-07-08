<?php

namespace App\Http\Controllers;

use App\Models\Challenge;
use App\Models\TestCase;
use Illuminate\Http\Request;

class TestCaseController extends Controller
{
    public function index(Request $request, Challenge $challenge)
    {
        $user = $request->user();

        $testCases = $user && $user->isAdmin()
            ? $challenge->testCases
            : $challenge->testCases()->where('is_hidden', false)->get();

        return response()->json($testCases);
    }

    public function store(Request $request, Challenge $challenge)
    {
        $this->authorize('create', TestCase::class);

        $data = $request->validate([
            'input'           => 'required|string',
            'expected_output' => 'required|string',
            'is_hidden'       => 'boolean',
        ]);

        $testCase = $challenge->testCases()->create($data);

        return response()->json($testCase, 201);
    }

    public function update(Request $request, TestCase $testCase)
    {
        $this->authorize('update', $testCase);

        $data = $request->validate([
            'input'           => 'sometimes|string',
            'expected_output' => 'sometimes|string',
            'is_hidden'       => 'sometimes|boolean',
        ]);

        $testCase->update($data);

        return response()->json($testCase);
    }

    public function destroy(TestCase $testCase)
    {
        $this->authorize('delete', $testCase);
        $testCase->delete();

        return response()->json(['message' => 'Test case deleted.']);
    }
}