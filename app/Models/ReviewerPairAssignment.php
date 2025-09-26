<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReviewerPairAssignment extends Model
{
    //
    protected $fillable = [
        'examinee_test_code',
        'reviewer_pair_id',
        'start_at',
        'finish_at',
    ];
}
