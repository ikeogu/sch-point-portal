<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;
use Auth;

class Teacher extends Model
{


    /**
     *teacherSubjects() method
     *@param $teacher_id gotten from $staff_id of Staff table
     *@return $details
     */
    public function teacherSubjects($id, $school_id)
    {
        $details = SubjectTeacher::with(['subject', 'classTeacher.c_class', 'classTeacher.level'])->where(['teacher_id' => $id, 'school_id' => $school_id])->get();
        return $details;
    }

    /**
     *teacherClasses() method
     *@param $teacher_id gotten from $staff_id of Staff table
     *@return $details
     */
    public function teacherClasses($id, $school_id)
    {
        $details = ClassTeacher::with(['level', 'c_class'])->where(['teacher_id' => $id, 'school_id' => $school_id])->get();
        return $details;
    }

    public function getClassId(array $options = [], $action = 'fetch')
    {
        if ($action == 'insert') {
            DB::table('class_teachers')->insert($options);
        }
        $teacher_class = CClass::join('class_teachers', 'classes.id', '=', 'class_teachers.class_id')
            ->where('class_teachers.school_id', $options['school_id'])
            ->where('class_teachers.class_id', $options['class_id'])
            ->where('class_teachers.section_id', $options['section_id'])
            ->where('class_teachers.level_id', $options['level_id'])
            ->select('class_teachers.id as class_teacher_id')
            ->get();

        return $teacher_class;
    }

    /**
     *teacherClassStudents() method
     *@param class_teachers.id as class_id in students table
     *@return $students
     */
    public function teacherClassStudents($class_teacher_id, $sess_id, $term_id, $school_id)
    {

        $students_in_class = StudentsInClass::with(['student' => function ($query) {
            $query->ActiveAndSuspended();
        }, 'student.user'])->where([
            'class_teacher_id' => $class_teacher_id,
            'sess_id' => $sess_id,
            //'term_id'=>$term_id,
            'school_id' => $school_id
        ])->get();

        $students = [];

        foreach ($students_in_class as $student_in_class) :
            if ($student_in_class->student !== null) {
                $student = $student_in_class->student;
                $student->skill = $student->skills()->where(['school_id' => $school_id, 'sess_id' => $sess_id, 'term_id' => $term_id])->first();

                $student->behavior = $student->behaviors()->where(['school_id' => $school_id, 'sess_id' => $sess_id, 'term_id' => $term_id])->first();
                $students[] = $student;
                // if ($student->studentship_status == 'active') {
                //     $students[] = $student;
                // }
            }

        endforeach;


        return $students;
    }


    /**
     *subjectStudents() method
     *@param subject_teacher_id as $id
     *@return $students in array format
     */
    public function teacherSubjectStudents($subject_teacher, $sess_id, $term_id, $school_id)
    {
        $student_offering_subjects_obj =  new StudentsOfferingSubject();
        $students =  $this->teacherClassStudents($subject_teacher->class_teacher_id, $sess_id, $term_id, $school_id);
        foreach ($students as $student) {
            if ($student != null) {
                $student_offering_subjects_obj->addStudentToSubjectClass($student->id, $subject_teacher->id, $sess_id, $term_id, $school_id);
            }
        }
        return $students;

        // return $this->teacherClassStudents($class_teacher_id, $sess_id, $term_id, $school_id);
        // $students_offering_subjects = StudentsOfferingSubject::with(['student' => function ($query) {
        //     $query->ActiveAndSuspended();
        // }, 'student.user'])->where([
        //     'subject_teacher_id' => $subject_teacher->id,
        //     'sess_id' => $sess_id,
        //     //'term_id'=>$term_id,
        //     'school_id' => $school_id
        // ])->get();

        // if ($students_offering_subjects->isEmpty()) {
        //     $students =  $this->teacherClassStudents($subject_teacher->class_teacher_id, $sess_id, $term_id, $school_id);
        //     foreach ($students as $student) {
        //         if ($student != null) {
        //             $student_offering_subjects_obj->addStudentToSubjectClass($student->id, $subject_teacher->id, $sess_id, $term_id, $school_id);
        //         }
        //     }
        //     return $students;
        // } else {

        //     $students = [];
        //     foreach ($students_offering_subjects as $student_in_class) :
        //         if ($student_in_class->student != null) {
        //             # code...

        //             $student = $student_in_class->student;
        //             $student->skill = $student->skills()->where(['school_id' => $school_id, 'sess_id' => $sess_id, 'term_id' => $term_id])->first();

        //             $student->behavior = $student->behaviors()->where(['school_id' => $school_id, 'sess_id' => $sess_id, 'term_id' => $term_id])->first();
        //             $students[] = $student;
        //         }
        //     endforeach;


        //     return $students;
        // }
    }



    /**
     *allMarkedAttendance() method
     *@param array $options with 'option', 'fromDate', 'toDate' and 'id' as keys
     *@return $marked students
     */
    public function allMarkedAttendance(array $options = [])
    {

        if ($options['option'] == 'class') {

            $marked =  ClassAttendance::where('class_teacher_id', $options['id'])
                ->where('student_ids', '!=', NULL)
                ->whereDate('created_at', '=', todayDate())
                ->select('id', 'student_ids', 'created_at as date')
                ->get();
            return $marked;
        }
        $marked = SubjectAttendance::where('subject_teacher_id', $options['id'])
            ->where('student_ids', '!=', NULL)
            ->whereDate('created_at', '=', todayDate())
            ->select('id', 'student_ids', 'created_at as date')
            ->get();
        return $marked;
    }
}
