<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Curriculum extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'school_id',
        'teacher_id',
        'subject_teacher_id',
        'title',
        'term_id',
        'curriculum'
    ];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function subjectTeacher()
    {
        return $this->belongsTo(SubjectTeacher::class);
    }



    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function teacher()
    {
        return $this->belongsTo(Staff::class, 'teacher_id', 'id');
    }

    /**
     *teacherCurricula() method
     *@param $teacher_id gotten from $staff_id of Staff table
     *@return $details
     */
    public function teacherCurricula($curricula)
    {

        foreach ($curricula as $curriculum) :

            $detail = SubjectTeacher::find($curriculum->subject_teacher_id);
            $subject_id = $detail->subject_id;
            $class_id = $detail->classTeacher->class_id;
            $teacher_id = $detail->teacher_id;

            $subject = Subject::find($subject_id);
            $class = CClass::find($class_id);
            $level = Level::find($class->level);
            if ($teacher_id != NULL) {
                $teacher = Staff::find($teacher_id);
                $curriculum->teacher = $teacher->user;
            }


            $curriculum->subject = $subject;
            $curriculum->class = $class;
            $curriculum->level = $level;

        endforeach;
        return $curricula;
    }
}
