<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MatchOverride extends Model
{
    protected $fillable = ['fixture_id', 'home_score', 'away_score', 'status', 'notes'];

    protected $casts = [
        'home_score' => 'integer',
        'away_score' => 'integer',
    ];
}
