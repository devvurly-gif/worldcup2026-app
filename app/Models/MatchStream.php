<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MatchStream extends Model
{
    protected $fillable = ['fixture_id', 'stream_url', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];
}
