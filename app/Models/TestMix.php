<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TestMix extends Model
{
    //
    use SoftDeletes;

    protected $fillable = [
        'id',
        'code',
        'test_group_id',
        'test_id',
        'test_root_id',
        'test_form_id',
        'subject_id',
        'content',
        'duration',
        'council_code',
        'council_turn_code',
        'is_used',
        'used_time',
    ];
}
