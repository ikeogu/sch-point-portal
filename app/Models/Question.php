<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = [];
    public function quizzes()
    {
        return $this->hasMany(Quiz::class);
    }
}
