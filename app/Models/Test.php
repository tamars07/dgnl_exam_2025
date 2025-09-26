<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Test extends Model
{
    //
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'desc',
        'test_root_numbers',
        'test_mix_numbers',
        'test_group_id',
        'test_form_id',
        'subject_id',
    ];
}
