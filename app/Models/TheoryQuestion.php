<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TheoryQuestion extends Model
{
    //
    protected $fillable = [];
    public function quizzes()
    {
        return $this->hasMany(Quiz::class);
    }
}
