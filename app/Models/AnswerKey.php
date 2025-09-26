<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnswerKey extends Model
{
    //
    protected $fillable = [
        'examinee_test_uuid',
        'examinee_answer_uuid',
        'examinee_test_code',
        'council_code',
        'council_turn_code',
        'subject_id',
        'question_id',
        'matrix_location',
        'question_type_id',
        'examinee_answer',
        'submitting_time',
        'answer_type',
        'answer_key',
        'question_mark_id',
        'rubric_id',
        'is_correct',
        'score',
        'is_assigned',
    ];
}
