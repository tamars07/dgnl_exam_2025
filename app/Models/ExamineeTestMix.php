<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExamineeTestMix extends Model
{
    //
    use SoftDeletes;

    protected $fillable = [
        'examinee_test_uuid',
        'examinee_test_code',
        'examinee_code',
        'examinee_account',
        'test_mix_id',
        'subject_id',
        'council_code',
        'council_turn_code',
        'room_code',
        'start_time',
        'expected_finish_time',
        'finish_time',
        'remaining_time',
        'bonus_time',
        'answer_logs',
    ];
}
