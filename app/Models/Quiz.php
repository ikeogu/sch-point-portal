<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    protected $fillable = [];
    public function question()
    {
        return $this->belongsTo(Question::class);
    }
    public function theoryQuestion()
    {
        return $this->belongsTo(TheoryQuestion::class);
    }
}
