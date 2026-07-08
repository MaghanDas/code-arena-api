<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TestCase extends Model
{
    protected $fillable = [
        'challenge_id', 'input', 'expected_output', 'is_hidden',
    ];

    protected $casts = [
        'is_hidden' => 'boolean',
    ];

    public function challenge()
    {
        return $this->belongsTo(Challenge::class);
    }
}