<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TestGroup extends Model
{
    //
    use SoftDeletes;
    protected $fillable = [
        'id',
        'code',
        'desc',
        'password',
        'no_subjects',
        'is_used',
        'packaged_by',
        'packaged_at',
        'status'
    ];
}
