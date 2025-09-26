<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends Model
{
    /** @use HasFactory<\Database\Factories\QuestionFactory> */
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'id',
        'cloze_id',
        'question_type_id',
        'subject_id',
        'question_mark_id',
        'competency_id',
        'taxonomy_id',
        'topic_id',
        'difficult_id',
        'uuid',
        'code',
        'pre_content',
        'content',
        'post_content',
        'option_1',
        'option_2',
        'option_3',
        'option_4',
        'option_5',
        'option_6',
        'option_7',
        'option_8',
        'option_9',
        'option_10',
        'min_words',
        'max_words',
        'answer_type',
        'no_options',
        'is_shuffled',
        'no_used',
        'last_used',
        'context_order'
    ];
}
