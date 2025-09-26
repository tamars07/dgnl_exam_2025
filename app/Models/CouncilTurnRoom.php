<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CouncilTurnRoom extends Model
{
    //

    protected $fillable = [
        'is_active',
        'monitor_code',
        'council_turn_code',
        'room_code',
    ];
}
