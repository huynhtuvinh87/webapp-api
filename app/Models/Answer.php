<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{

    protected $fillable = [
        'requirement_history_id',
        'question_id',
        'answer_text',
        'correct_answer'
    ];

}
