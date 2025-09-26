<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CouncilTurnTestMix extends Model
{
    //
    protected $fillable = [
        // 'id',
        'test_mix_id',
        'subject_id',
        'council_code',
        'council_turn_code',
        'is_used',
        'used_time',
        'is_active_by_chairman',
    ];
}
