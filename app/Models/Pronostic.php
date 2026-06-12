<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pronostic extends Model
{
    protected $fillable = ['user_id', 'fixture_id', 'home_score', 'away_score', 'points'];

    public function user() { return $this->belongsTo(User::class); }
}
