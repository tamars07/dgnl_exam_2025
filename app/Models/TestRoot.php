<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TestRoot extends Model
{
    //
    use SoftDeletes;

    protected $fillable = [
        'code',
        'test_group_id',
        'test_id',
        'test_form_id',
        'subject_id',
        'content',
        'is_used'
    ];
}
