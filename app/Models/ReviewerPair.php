<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReviewerPair extends Model
{
    //
    protected $fillable = [
        'subject_id',
        'code',
        'no_tests',
        'start_at',
        'finish_at',
    ];
}
