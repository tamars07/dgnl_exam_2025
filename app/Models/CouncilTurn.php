<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CouncilTurn extends Model
{
    //
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'start_at',
        'no_rooms',
        'is_active',
        'is_backup',
        'bk_file_name',
        'council_code'
    ];
}
