<?php

namespace App\Http\Controllers\Setup;

use App\Http\Controllers\Controller;

use App\Models\Assignment;
use App\Models\CClass;
use App\Models\Qualification;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Staff;
use App\Models\Teacher;
use App\Models\SubjectTeacher;
use App\Models\ClassTeacher;
use App\Models\StudentsInClass;
use App\Models\Behavior;
use App\Models\Skill;
use App\Models\Term;
use App\Models\Result;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Laracasts\Flash\Flash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class TeachersController extends Controller
{


    private function getAnalysis($subject_id)
    {
        $terms = Term::select('name', 'id')->get();
        $this->good = 0;
        $results = array();
        foreach ($terms as $term) {
            $result = array();
            $result['name'] = $term->name;
            $result['average'] = Result::where('term_id', $term->id)
                ->where('subject_teacher_id', $subject_id)
                ->avg('total');

            $result['average'] = $result['average'] == null ? 0 : $result['average'];

            Result::where('term_id', $term->id)
                ->where('subject_teacher_id', $subject_id)
                ->each(
                    function ($item, $key) use ($result) {
                        if ($item['total'] > $result['average']) {
                            $this->good += 1;
                        }
                        return true;
                    }
                );
            array_push($results, $result);
        }
        return $results;
    }

    private $good = 0;

    function analyse($totals)
    {
        $result = array();
        $fails = 0;
        $least = $this->getLeastGrade();
        if ($totals->count() == 0)
            return ['average' => 0, 'fails' => 0];
        $sum = 0;
        foreach ($totals as $total) {
            if ($total <= $least)
                $fails += 1;

            $sum += $total;
        }
        $result['average'] = $sum / $totals->count();
        $result['fails'] = $fails;
        return $result;
    }

    public function subjectStudents($subject_teacher_id)
    {
        $teacher = new Teacher();

        $subject_teacher = SubjectTeacher::find($subject_teacher_id);
        $class_teacher_id = $subject_teacher->class_teacher_id;

        $class = CClass::find($subject_teacher->classTeacher->class_id);

        $sess_id = $this->getSession()->id;
        $term_id = $this->getTerm()->id;
        $school_id = $this->getSchool()->id;
        list($student_ids_arr, $students) = $teacher->teacherSubjectStudents($class_teacher_id, $sess_id, $term_id, $school_id);
        /*return $students;
        $student_array = [];
        foreach ($subject_class_students as $student) :

            //fetch students that have not been registered already
            if (!in_array($student->id, $student_ids_arr)) {
               $student_array[$student->id] = $student->user->first_name.' '.$student->user->last_name;
            }

        endforeach;*/

        return $this->render('core::teachers.subject_students', compact('students', 'class', 'subject_teacher'));
    }

    public function teachers()
    {
        //DB::enableQueryLog();
        $class = Student::find(auth()->user()->account_holder_id);
        $teachers = Teacher::join('class_teacher', 'teachers.id', '=', 'class_teacher.teacher_id')
            ->join('subject_teacher', 'teachers.id', '=', 'subject_teacher.teacher_id')
            ->join('subjects', 'subject_teacher.subject_id', '=', 'subjects.id')
            ->where('class_teacher.class_id', $class->class_id)
            ->where('subjects.level', $class->level)
            ->select('teachers.id', 'teachers.email', 'teachers.first_name', 'teachers.last_name', 'subjects.name as subject_name')
            ->get();
        //dd($teachers);
        //dd(DB::getQueryLog());
        return $this->render('core::teachers.class_teachers', compact('teachers'));
    }
}
