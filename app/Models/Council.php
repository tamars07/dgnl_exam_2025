<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Council extends Model
{
    //
    use SoftDeletes;

    protected $fillable = [
        'code',
        'desc',
        'no_turns',
        'start_at',
        'finish_at',
        'is_autostart',
        'import_testdata_before_time',
        'is_backup',
        'is_clear',
        'status',
        'organization_code',
        'monitor_id',
    ];
}
