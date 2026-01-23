<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuestionMark extends Model
{
    //
    use SoftDeletes;

    protected $fillable = [
        'id',
        'name',
        'desc',
        'value',
        'status',
    ];
}
