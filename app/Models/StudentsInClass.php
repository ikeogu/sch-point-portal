<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class StudentsInClass extends Model
{
    // protected static function booted()
    // {
    //     static::addGlobalScope('active_suspended', function (Builder $builder) {
    //         $builder->with(['student' => function ($q) {
    //             $q->whereIn('studentship_status', ['active', 'suspended']);
    //         }]);
    //     });
    // }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function classTeacher()
    {
        return $this->belongsTo(ClassTeacher::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function session()
    {
        return $this->belongsTo(SSession::class, 'sess_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function term()
    {
        return $this->belongsTo(Term::class);
    }

    /**
     *This method adds student to a class for a term in a session
     **/
    public function addStudentToClass($student_id, $class_teacher_id, $sess_id, $term_id, $school_id)
    {
        $student_in_class = StudentsInClass::where([
            // 'class_teacher_id' => $class_teacher_id,
            'sess_id' => $sess_id,
            'student_id' => $student_id,
            'school_id' => $school_id
        ])->first();

        if (!$student_in_class) {
            $student_in_class = new StudentsInClass();
            $student_in_class->sess_id = $sess_id;
            $student_in_class->school_id = $school_id;
            $student_in_class->student_id = $student_id;
        }

        $student_in_class->class_teacher_id = $class_teacher_id;


        $student_in_class->save();
    }

    /**
     *This method removes student from a class already assigned to him/her
     **/

    public function removeStudentFromClass($student_id, $sess_id, $term_id, $school_id)
    {

        $student_in_class = $this->fetchStudentInClass($student_id, $sess_id, $term_id, $school_id);
        $student_ids = "";
        if ($student_in_class) {
            $student_ids = $student_in_class->student_ids;

            $new_students = deleteSingleElementFromString($student_ids, $student_id);
            $student_in_class->student_ids = $new_students;
            $student_in_class->save();
        }
    }

    public function fetchStudentInClass($student_id, $sess_id, $term_id, $school_id)
    {

        $student_in_class = StudentsInClass::with('classTeacher.c_class', 'classTeacher.subjectTeachers.subject')->where([
            'school_id' => $school_id,
            'sess_id' => $sess_id,
            'student_id' => $student_id,
        ])->first();

        return $student_in_class;
    }
}
