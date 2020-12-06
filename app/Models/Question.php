<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = [
        'question_text',
        'reference',
        'option_1',
        'option_2',
        'option_3',
        'option_4',
        'correct_answer'
    ];

    /**
     * Get array of options
     * @return array
     */
    public function getOptionsAttribute(){

        $question = $this->attributes;

        return [
            $question['option_1'] ?? null,
            $question['option_2'] ?? null,
            $question['option_3'] ?? null,
            $question['option_4'] ?? null
        ];

    }

    public function answers(){
        return $this->hasMany(Answer::class);
    }

    public function test(){
        return $this->belongsTo(Test::class);
    }
}
