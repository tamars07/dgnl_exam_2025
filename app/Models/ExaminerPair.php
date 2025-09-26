<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExaminerPair extends Model
{
    //
    protected $fillable = [
        'council_code',
        'council_turn_code',
        'subject_id',
        'code',
        'no_tests',
        'start_at',
        'finish_at',
    ];
}
