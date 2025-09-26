<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExaminerAssignment extends Model
{
    //
    protected $fillable = [
        'examinee_test_uuid',
        'examinee_answer_uuid',
        'examinee_test_code',
        'subject_id',
        'examiner_id',
        'examiner_pair_id',
        'answer_key_id',
        'start_at',
        'finish_at',
        'is_done',
        'is_review',
    ];
}
