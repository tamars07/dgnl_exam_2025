<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExamineeAnswer extends Model
{
    //
    use SoftDeletes;

    protected $fillable = [
        'examinee_test_uuid',
        'examinee_answer_uuid',
        'examinee_code',
        'examinee_account',
        'test_mix_id',
        'subject_id',
        'question_id',
        'question_type_id',
        'question_number',
        'answer_detail',
        'submitting_time',
        'remaining_time',
    ];
}
