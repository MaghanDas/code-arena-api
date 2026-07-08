<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Submission extends Model
{
    protected $fillable = [
        'user_id', 'challenge_id', 'contest_id', 'code',
        'language', 'status', 'score', 'runtime_ms',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function challenge()
    {
        return $this->belongsTo(Challenge::class);
    }

    public function contest()
    {
        return $this->belongsTo(Contest::class);
    }
}