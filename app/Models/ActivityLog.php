<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ActivityLog extends Model
{
    //
    use SoftDeletes;
    protected $fillable = [
        // 'id',
        'log_uuid',
        'username',
        'council_code',
        'council_turn_code',
        'room_code',
        'role_id',
        'action',
        'desc',
        'log_time',
    ];
}
