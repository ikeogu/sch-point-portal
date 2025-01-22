<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuardianStudent extends Model
{
    //
    public function student() {
        return $this->belongsTo(Student::class, 'student_id', 'id');
    }
    public function guardian() {
        return $this->belongsTo(Guardian::class, 'guardian_id', 'id');
    }
}
