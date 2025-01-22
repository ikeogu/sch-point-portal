<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Routine extends Model
{
    protected $fillable = [
        'school_id',
        'class_teacher_id',
        'subject_teacher_id',
        'teacher_id',
        'day',
        'start',
        'end',
        'all_day'
    ];

    public function subjectTeacher()
    {
        return $this->belongsTo(SubjectTeacher::class);
    }


    public function classTeacher()
    {
        return $this->belongsTo(ClassTeacher::class);
    }
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function timeTable($id)
    {
        $options = [];
        $subject_teachers = SubjectTeacher::where('teacher_id', $id)->get();
        foreach ($subject_teachers as $subject_teacher) :
            $subject_teacher_id = $subject_teacher->id;
            $routines = Routine::with('subjectTeacher.staff.user', 'subjectTeacher.subject', 'subjectTeacher.classTeacher.c_class')->where('subject_teacher_id', $subject_teacher_id)->get();
            //$events = [];

            foreach ($routines as $routine) {
                $options[] =  $routine;
            }
        //$options[] = $events;
        endforeach;

        return $options;
    }
}
