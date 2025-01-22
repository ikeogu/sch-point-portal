<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizCompilation extends Model
{
    protected $fillable = [];
    public function quizzes()
    {
        return $this->hasMany(Quiz::class);
    }
    public function quizAttempts()
    {
        return $this->hasMany(QuizAttempt::class);
    }
    public function quizAnswers()
    {

        return $this->hasManyThrough(QuizAnswer::class, QuizAttempt::class);
    }

    public function subjectTeacher()
    {
        return $this->belongsTo(SubjectTeacher::class);
    }
}
