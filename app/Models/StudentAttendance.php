<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentAttendance extends Model
{
    protected $fillable = [
        'teacher_id',
        'class_id',
        'subject_id',
        'student_id',
        'date',
        'attendance'
    ];

    protected $dates = ['date'];
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function subject() {
        return $this->belongsTo(Subject::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cClass() {
        return $this->belongsTo(CClass::class, 'class_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function student() {
        return $this->belongsTo(Student::class, 'student_id', 'id');
    }

}
