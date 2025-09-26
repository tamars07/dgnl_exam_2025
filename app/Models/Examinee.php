<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Examinee extends Model
{
    //
    use SoftDeletes;

    protected $fillable = [
        'code',
        'id_card_number',
        'lastname',
        'firstname',
        'birthday',
        'password',
        'seat_number',
        'subject_id',
        'council_code',
        'council_turn_code',
        'room_code',
        'user_id',
        'role_id',
        'is_backup',
        'status'
    ];
}
