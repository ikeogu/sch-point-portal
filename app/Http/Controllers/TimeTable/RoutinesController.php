<?php

namespace App\Http\Controllers\TimeTable;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Models\CClass;
use App\Models\Routine;
use App\Models\SSession;
use App\Models\Subject;
use App\Models\ClassTeacher;
use App\Models\SubjectTeacher;
use App\Models\StudentsInClass;
use App\Models\Staff;

class RoutinesController extends Controller
{
    public function fetchClasses()
    {
        $levels = $this->getLevels();
        $level_ids = array_map(
            function ($level) {
                return $level['id'];
            },
            $levels->toArray()
        );
        $class_teachers = ClassTeacher::with('c_class', 'subjectTeachers.subject', 'subjectTeachers.staff.user')->whereIn('level_id', $level_ids)->where('school_id', $this->getSchool()->id)->orderBy('level_id')->get();
        return $this->render(compact('class_teachers'));
    }

    public function fetchClassRoutine($class_teacher_id)
    {
        $school_id = $this->getSchool()->id;

        $class_teacher = ClassTeacher::with('c_class', 'subjectTeachers.subject', 'subjectTeachers.staff.user', 'routines.subjectTeacher.staff.user', 'routines.subjectTeacher.subject')->where('school_id', $this->getSchool()->id)->find($class_teacher_id);
        return $this->render(compact('class_teacher'));
    }
    public function create($class_teacher_id)
    {
        $classes = ClassTeacher::where('school_id', $this->getSchool()->id)->get();
        $this_class = ClassTeacher::find($class_teacher_id);
        $routines = $this->fetchRoutine($class_teacher_id);
        $subjects = SubjectTeacher::where('class_teacher_id', $class_teacher_id)->get();


        $subject_array = [];
        foreach ($subjects as $subject) :
            $teacher = Staff::find($subject->teacher_id);
            if ($teacher) {
                if ($subject->subject) {
                    $subject_array[$subject->id] = $subject->subject->name . ' by (' . $teacher->user->first_name . ' ' . $teacher->user->last_name . ')';
                }
            }

        endforeach;
        return $this->render('timetable::routines.class_routine', compact('classes', 'routines', 'subject_array', 'this_class'));
    }

    public function store(Routine $routine)
    {
        $inputs = request()->all();
        $subject_teacher_id = $inputs['subject_teacher_id'];
        $class_teacher_id = $inputs['class_teacher_id'];
        $teacher_id = SubjectTeacher::find($subject_teacher_id)->teacher_id;
        $day = $inputs['day'];
        $start = date('H:i:s', strtotime($inputs['start']));
        $end = date('H:i:s', strtotime($inputs['end']));
        $teacher_is_busy = Routine::where(['teacher_id' => $teacher_id, 'day' => $day])
            ->where('subject_teacher_id', '!=', $subject_teacher_id)
            ->where('start', '<=', $start)
            ->where('end', '>', $start)
            ->first();
        if ($teacher_is_busy) {
            //return 'busy';
            return response()->json([
                'message' => 'busy',
                'url' => '/routine/teacher/time-table/' . $teacher_id
            ]);
        }
        $teacher_is_free = Routine::where(['teacher_id' => $teacher_id, 'day' => $day, 'subject_teacher_id' => $subject_teacher_id, 'class_teacher_id' => $class_teacher_id])
            ->where('start', '<=', $start)
            ->where('end', '>', $start)
            ->first();
        if (!$teacher_is_free) {
            $routine->create([
                'school_id' => $this->getSchool()->id,
                'class_teacher_id' => $class_teacher_id,
                'subject_teacher_id' => $subject_teacher_id,
                'teacher_id' => $teacher_id,
                'day' => $day,
                'all_day' => 0, //$inputs['all_day'],
                'start' => $start,
                'end' => $end
            ]);
        }
        return $this->fetchClassRoutine($class_teacher_id);


        /*foreach($events as $event):
            echo $event;
        endforeach;*/

        //redirect()->route('get_routine', ['class_id'=>$class_teacher_id]);
    }

    public function updateRoutine()
    {
        $inputs = request()->all();
        $id = $inputs['id'];
        $day = date('l', strtotime($inputs['day']));
        // $start = $inputs['start'];
        // $end = $inputs['end'];
        $start = date('H:i:s', strtotime($inputs['start']));
        $end = date('H:i:s', strtotime($inputs['end']));

        $day = schoolDaysStr($day); //from helpers fetch day number from day str


        $routine = Routine::find($id);
        $routine->update([
            'start' => $start,
            'end'   => $end,
            'day'   => $day
        ]);

        /*$class_teacher_id =  $routine->class_teacher_id;
        return $routines = $this->fetchRoutine($class_teacher_id);*/
    }

    public function destroy($id)
    {
        $routine = Routine::find($id);
        $routine->delete();
        // return $routines = $this->fetchRoutine($class_teacher_id);
    }

    /*public function getClassSubjects($class_id)
    {
        $subjects = SubjectTeacher::where('class_teacher_id', $class_id)->get();

        $data = Routine::where('class_teacher_id', $class_id)->get();
        $events = [];
        foreach ($data as $record) {
            $staff = Staff::find($record->subjectTeacher->teacher_id);
            $subject = Subject::find($record->subjectTeacher->subject_id);
            array_push($events, array(
                'id' => $record->id,
                'title' => $subject->name . ' - ' . $staff->user->first_name . ' ' . $staff->user->last_name,
                'url' => '/routine/update/'.$record->id,
                'start' => $record->start,
                'end' => $record->end,
                'day' => $record->day,
                'allDay' => $record->allDay
            ));
        }
        return response()->json([
            'subjects' => $subjects,
            'events' => $events
        ]);
    }*/

    public function teacherTimeTable()
    {
        $id = $this->getStaff()->id;
        $routine_obj = new Routine();

        $routines =  $routine_obj->timeTable($id);
        //$routines = $this->fetchRoutine($class_teacher_id, $options);
        return $this->render(compact('routines'));
    }

    public function classTimeTable()
    {
        $school_id = $this->getSchool()->id;
        $sess_id = $this->getSession()->id;
        $term_id = $this->getTerm()->id;
        $student_in_class_obj = new StudentsInClass();

        $request = request()->all();

        if (isset($request['student_id']) && $request['student_id'] != "") {
            $student_id = $request['student_id'];
        } else {
            //it means this is a student
            $student_id = $this->getStudent()->id;
        }
        $student_in_class = $student_in_class_obj->fetchStudentInClass($student_id,  $sess_id, $term_id, $school_id);

        $id = $student_in_class->class_teacher_id;
        $routines = Routine::with('subjectTeacher.staff.user', 'subjectTeacher.subject')->where('class_teacher_id', $id)->get();
        // $options = [];
        // //$events = [];
        // foreach ($data as $record) :
        //     $subject = Subject::find($record->subjectTeacher->subject_id);
        //     $class = CClass::find($record->classTeacher->class_id);
        //     $options[] =  array(
        //         'id' => $record->id,
        //         'title' => $subject->name . ' (' . $class->name . ')',
        //         'start' => $record->start,
        //         'end' => $record->end,
        //         'background_color' => $record->subjectTeacher->subject->color_code,
        //         'dow' => $record->day
        //     );
        // endforeach;

        // $routines =  response()->json([
        //     'events' => $options
        // ]);

        $class_teacher = ClassTeacher::find($id);
        //$routines = $this->fetchRoutine($class_teacher_id, $options);
        return $this->render(compact('routines', 'class_teacher'));
    }
}
