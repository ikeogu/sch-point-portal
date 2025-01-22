<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubjectTeacher extends Model
{
    use SoftDeletes;
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
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function questions()
    {
        return $this->hasMany(Question::class);
    }
    public function theoryQuestions()
    {
        return $this->hasMany(TheoryQuestion::class);
    }
    public function quizCompilations()
    {
        return $this->hasMany(QuizCompilation::class);
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function classTeacher()
    {
        return $this->belongsTo(ClassTeacher::class);
    }
    public function curriculum()
    {
        return $this->hasOne(Curriculum::class);
    }

    public function materials()
    {
        return $this->hasMany(Material::class);
    }

    public function dailyClassrooms()
    {
        return $this->hasMany(DailyClassroom::class);
    }



    public function getSubjectTeachers($subject_teachers)
    {


        foreach ($subject_teachers as $subject_teacher) :
            if (!empty($subject_teacher)) {
                if ($subject_teacher->subject) {
                    $curriculum_level_group_id = $subject_teacher->subject->curriculum_level_group_id;

                    //$level_id = $subject_teacher->subject->level_id;




                    $subject_teacher->stud_count = 0;
                    if ($subject_teacher->student_ids != '' || $subject_teacher->student_ids != NULL) {
                        $students = explode('~', $subject_teacher->student_ids);
                        $subject_teacher->stud_count = count($students);
                    }

                    $subject_teacher->class = NULL;
                    if ($subject_teacher->class_teacher_id != NULL || $subject_teacher->class_teacher_id != '') {
                        $class_id = $subject_teacher->classTeacher->class_id;
                        $subject_teacher->class = CClass::find($class_id);
                    }

                    $subject_teacher->teacher = NULL;
                    if ($subject_teacher->teacher_id != NULL || $subject_teacher->teacher_id != '') {
                        if ($subject_teacher->staff) {
                            $user_id = $subject_teacher->staff->user_id;
                            $subject_teacher->teacher = User::find($user_id);
                        }
                    }
                    //$subject_teacher->level = Level::find($level_id);

                    /*$level_classes = ClassTeacher::join('classes', 'class_teachers.class_id', '=', 'classes.id')
                    ->where('classes.level',$level_id)
                    ->select('class_teachers.id as id','classes.name as name')
                    ->get();
                    $subject_teacher->level_classes = $level_classes;*/
                }
            }
        endforeach;



        return $subject_teachers;
    }
}
