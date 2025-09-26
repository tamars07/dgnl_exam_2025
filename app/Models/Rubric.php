<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rubric extends Model
{
    //
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'desc',
        'status',
    ];
}
