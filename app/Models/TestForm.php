<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TestForm extends Model
{
    //
    use SoftDeletes;
    protected $fillable = [
        'id',
        'code',
        'name',
        'desc',
        'no_questions',
        'no_parts'
    ];
}
