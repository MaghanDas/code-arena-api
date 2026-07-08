<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Challenge extends Model
{
    protected $fillable = [
        'title', 'description', 'difficulty',
        'time_limit', 'created_by', 'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function testCases()
    {
        return $this->hasMany(TestCase::class);
    }

    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }

    public function contests()
    {
        return $this->belongsToMany(Contest::class, 'contest_challenges');
    }
}