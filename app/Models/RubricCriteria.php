<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RubricCriteria extends Model
{
    //
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'desc',
        'rubric_id',
        'min_score',
        'max_score',
        'scores',
        'status',
    ];
}
