<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExaminerRubricDetail extends Model
{
    //
    protected $fillable = [
        'examiner_id',
        'examinee_test_uuid',
        'examinee_answer_uuid',
        'examinee_test_code',
        'subject_id',
        'question_id',
        'matrix_location',
        'question_type_id',
        'rubric_id',
        'rubric_criteria_id',
        'score',
    ];
}
