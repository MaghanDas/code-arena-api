<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContestParticipant extends Model
{
    protected $fillable = [
        'contest_id', 'user_id', 'total_score', 'rank',
    ];

    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function contest()
    {
        return $this->belongsTo(Contest::class);
    }
}