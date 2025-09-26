<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TestPart extends Model
{
    //
    use SoftDeletes;
    protected $fillable = [
        'id',
        'name',
        'part_title',
        'desc',
        'caltype',
        'is_shuffled',
        'status',
    ];
}
