<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subject extends Model
{
    //
    use SoftDeletes;

    protected $fillable = [
        'code',
        'short_code',
        'code_number',
        'name',
        'desc',
        'status',
    ];
}
