<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryScale extends Model
{
    use HasFactory;

    public function school()
    {
        return $this->belongsTo(School::class);
    }
    public function staff()
    {
        return $this->hasMany(Staff::class);
    }
}
