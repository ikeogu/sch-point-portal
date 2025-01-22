<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClassTeacher extends Model
{
    use SoftDeletes;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function school()
    {
        return $this->belongsTo(School::class);
    }
    public function routines()
    {
        return $this->hasMany(Routine::class);
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function c_class()
    {
        return $this->belongsTo(CClass::class, 'class_id', 'id');
    }

    public function firstStudentInClass()
    {
        return $this->hasOne(StudentsInClass::class);
    }
    public function studentsInClass()
    {
        return $this->hasMany(StudentsInClass::class);
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function staff()
    {
        return $this->belongsTo(Staff::class, 'teacher_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function level()
    {
        return $this->belongsTo(Level::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     *The return value is in array form and a foreach loop should be used to fetch each element
     */
    public function classActivity()
    {
        return $this->hasMany(ClassActivity::class);
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     *The return value is in array form and a foreach loop should be used to fetch each element
     */
    public function subjectTeachers()
    {
        return $this->hasMany(SubjectTeacher::class);
    }


    public function getClassTeachers($class_teachers, $sess_id, $term_id, $school_id)
    {
        if ($class_teachers != '[]') {
            foreach ($class_teachers as $class_teacher) :

                $level_id = $class_teacher->c_class->level;
                $pre_level_id = $class_teacher->c_class->pre_level;



                if ($class_teacher->teacher_id != NULL || $class_teacher->teacher_id != '') {
                    if ($class_teacher->staff) {
                        $user_id = $class_teacher->staff->user_id;
                        $class_teacher->teacher = User::find($user_id);
                    }
                }
                $class_teacher->level = Level::find($level_id);
                $class_teacher->pre_level = Level::find($pre_level_id);

                $student_in_class = StudentsInClass::where([
                    'class_teacher_id' => $class_teacher->id,
                    'sess_id' => $sess_id,
                    'term_id' => $term_id,
                    'school_id' => $school_id
                ])->first();
                $count = 0;
                if ($student_in_class) {
                    $student_ids_array = explode('~', $student_in_class->student_ids);
                    $count = count($student_ids_array);
                }
                $class_teacher->stud_count = $count;



            endforeach;
        }



        return $class_teachers;
    }
}
