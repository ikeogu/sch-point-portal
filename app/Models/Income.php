<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Income extends Model
{
    //
    public function student() {
        return $this->belongsTo(Student::class, 'paid_by', 'id');
    }

    public function staff() {
        return $this->belongsTo(Staff::class, 'paid_by', 'id');
    }
}
