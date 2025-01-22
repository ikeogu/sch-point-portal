<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizAttempt extends Model
{
    protected $fillable = [];
    public function quizAnswers()
    {
        return $this->hasMany(QuizAnswer::class);
    }
    public function quizCompilation()
    {
        return $this->belongsTo(QuizCompilation::class);
    }
    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
