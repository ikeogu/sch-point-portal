<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Remark extends Model
{

    protected $fillable = [
        'school_id',
        'student_id',
        'teacher_id',
        'class_teacher_id',
        'sub_term',
        'term_id',
        'sess_id',
        'class_teacher_remark',
        'head_teacher_remark',
        
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function student() {
        return $this->belongsTo(Student::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function teacher() {
        return $this->belongsTo(Teacher::class);
    }
    public function term() {
        return $this->belongsTo(Term::class);
    }
    public function sess() {
        return $this->belongsTo(SSession::class);
    }
    public function school() {
        return $this->belongsTo(School::class);
    }
}
