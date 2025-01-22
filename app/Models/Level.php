<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Level extends Model
{
    //
    use SoftDeletes;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function levelGroup()
    {
        return $this->belongsTo(CurriculumLevelGroup::class, 'curriculum_level_group_id', 'id');
    }
    public function classTeachers()
    {
        return $this->hasMany(ClassTeacher::class);
    }
    public function studentsInClass()
    {
        return $this->hasManyThrough(StudentsInClass::class, ClassTeacher::class);
    }
    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function getLevelClasses($level_id, $school_id)
    {
        $curriculum_level_group_id = Level::find($level_id)->curriculum_level_group_id;
        $subjects = Subject::where('curriculum_level_group_id', $curriculum_level_group_id)->get();

        $classes = CClass::join('class_teachers', 'class_teachers.class_id', '=', 'classes.id')
            ->where('classes.level', $level_id)
            ->where('class_teachers.school_id', $school_id)
            ->select('class_teachers.id as id', 'classes.name as name', 'classes.id as class_id', 'classes.section as section')->get();


        $selected_subjects = [];
        foreach ($subjects as $subject) :
            $selected_subjects[] = array('id' => $subject->id, 'name' => $subject->name . ' (' . $subject->code . ')');
        endforeach;

        $selected_classes = [];
        foreach ($classes as $class) :
            $selected_classes[] = array('id' => $class->id, 'name' => $class->name, 'section' => $class->section);
        endforeach;

        //$selected_subjects =  json_encode($selected_subjects);
        //$selected_classes =  json_encode($selected_classes);

        $fetched_data = array('subject' => $selected_subjects, 'class' => $selected_classes);

        return response()->json($fetched_data);
    }

    public function getLevelStudent($level_id, $school_id)
    {
        $students = Student::where(['school_id' => $school_id, 'current_level' => $level_id])->get();



        $student_level = [];
        foreach ($students as $student) :
            $student_level[] = array('id' => $student->id, 'details' => $student->registration_no . ' | ' . $student->user->last_name . ', ' . $student->user->first_name);
        endforeach;

        //$selected_subjects =  json_encode($selected_subjects);
        //$selected_classes =  json_encode($selected_classes);

        $fetched_data = array('students' => $student_level);

        return response()->json($fetched_data);
    }
}
