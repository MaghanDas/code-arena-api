<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Challenge;
use App\Models\TestCase;
use App\Models\Contest;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user
        $admin = User::create([
            'name'     => 'Admin',
            'email'    => 'admin@codearena.com',
            'password' => bcrypt('password'),
            'role'     => 'admin',
            'score'    => 0,
        ]);

        // Regular users
        $user1 = User::create([
            'name'     => 'Alice',
            'email'    => 'alice@codearena.com',
            'password' => bcrypt('password'),
            'role'     => 'user',
            'score'    => 150,
        ]);

        $user2 = User::create([
            'name'     => 'Bob',
            'email'    => 'bob@codearena.com',
            'password' => bcrypt('password'),
            'role'     => 'user',
            'score'    => 80,
        ]);

        // Challenges
        $c1 = Challenge::create([
            'title'        => 'Sum of Two Numbers',
            'description'  => 'Given two integers a and b, return their sum.',
            'difficulty'   => 'easy',
            'time_limit'   => 30,
            'created_by'   => $admin->id,
            'is_published' => true,
        ]);

        $c2 = Challenge::create([
            'title'        => 'Reverse a String',
            'description'  => 'Given a string s, return it reversed.',
            'difficulty'   => 'easy',
            'time_limit'   => 30,
            'created_by'   => $admin->id,
            'is_published' => true,
        ]);

        $c3 = Challenge::create([
            'title'        => 'FizzBuzz',
            'description'  => 'Print numbers 1–N. Multiples of 3 → Fizz, 5 → Buzz, both → FizzBuzz.',
            'difficulty'   => 'medium',
            'time_limit'   => 60,
            'created_by'   => $admin->id,
            'is_published' => true,
        ]);

        // Test cases
        TestCase::insert([
            // For "Sum of Two Numbers" — the code reads two numbers and prints sum
// ['challenge_id' => $c1->id, 'input' => "1 2",   'expected_output' => "3"],
// ['challenge_id' => $c1->id, 'input' => "10 20",  'expected_output' => "30"],
// ['challenge_id' => $c1->id, 'input' => "-5 5",   'expected_output' => "0", 'is_hidden' => true],
            // ['challenge_id' => $c1->id, 'input' => '1 2',   'expected_output' => '3',  'is_hidden' => false, 'created_at' => now(), 'updated_at' => now()],
            // ['challenge_id' => $c1->id, 'input' => '10 20', 'expected_output' => '30', 'is_hidden' => false, 'created_at' => now(), 'updated_at' => now()],
            // ['challenge_id' => $c1->id, 'input' => '-5 5',  'expected_output' => '0',  'is_hidden' => true,  'created_at' => now(), 'updated_at' => now()],

            // ['challenge_id' => $c2->id, 'input' => 'hello', 'expected_output' => 'olleh', 'is_hidden' => false, 'created_at' => now(), 'updated_at' => now()],
            // ['challenge_id' => $c2->id, 'input' => 'world', 'expected_output' => 'dlrow', 'is_hidden' => true,  'created_at' => now(), 'updated_at' => now()],

            // ['challenge_id' => $c3->id, 'input' => '15', 'expected_output' => "1\n2\nFizz\n4\nBuzz\nFizz\n7\n8\nFizz\nBuzz\n11\nFizz\n13\n14\nFizzBuzz", 'is_hidden' => false, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Contest
        $contest = Contest::create([
            'title'       => 'Beginner Blitz',
            'description' => 'A beginner-friendly contest with easy challenges.',
            'starts_at'   => now()->subHour(),
            'ends_at'     => now()->addHours(2),
            'created_by'  => $admin->id,
        ]);

        $contest->challenges()->attach([$c1->id, $c2->id, $c3->id]);
    }
}