<?php

namespace App\Http\Controllers\Attendance;

use App\Models\Staff;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\Subject;
use App\Models\CClass;
use App\Models\SubjectAttendance;
use App\Models\ClassAttendance;
use App\Models\SubjectTeacher;
use App\Models\ClassTeacher;
use App\Models\School;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\StudentsInClass;

class AttendanceController extends Controller
{
    public function index()
    {
        return $this->render('attendance.index');
    }

    public function classes()
    {
        $class_teachers = $this->getClassTeachers();

        return $this->render(compact('class_teachers'));
    }

    public function subjects()
    {
        $school_id = $this->getSchool()->id;

        $id = $this->getStaff()->id;

        $subject_teachers = SubjectTeacher::with(['subject', 'classTeacher.c_class'])->where(['teacher_id' => $id, 'school_id' => $school_id])->get();


        return $this->render(compact('subject_teachers'));
    }
    public function createClassAttendance(Request $request)
    {
        $teacher = new Teacher();
        $class_attendance_obj = new ClassAttendance();
        $sess_id = $this->getSession()->id;
        $term_id = $this->getTerm()->id;
        $school_id = $this->getSchool()->id;
        $class_teacher_id = $request->class_teacher_id;

        //echo $option_id;exit;
        //$class_teacher_id = $option_id;

        //$class_teacher = ClassTeacher::find($class_teacher_id);
        $students = $teacher->teacherClassStudents($class_teacher_id, $sess_id, $term_id, $school_id);
        //$fromDate = fromDate();
        $toDate = toDate();
        if ($request->date) {
            $toDate = $request->date;
        }
        $day = (int) (date('d', strtotime($toDate)));
        $month = date('m', strtotime($toDate));
        $year = date('Y', strtotime($toDate));

        $day_of_week = date('l', strtotime($toDate));
        $date_in_words = 'Take Attendance for ' . getDateFormatWords($toDate);
        $month_and_year = 'Attendance for ' . date('F, Y', strtotime($toDate));
        $no_of_days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);



        $options = array(
            'option' => 'class', //$option,
            //'fromDate'=>$fromDate,
            'toDate' => $toDate,
            'id' => $class_teacher_id
        );

        list($marked_month_attendances, $marked_today, $marked_student_array, $attendance_id) = $class_attendance_obj->markedAttendance($options);

        $absent_students = [];
        $present_students = [];
        if (isset($marked_student_array[$day])) {
            foreach ($students as $student) {


                if (in_array($student->id, $marked_student_array[$day])) {
                    $present_students[] = $student;
                } else {
                    $absent_students[] = $student;
                }
            }
        } else {
            $absent_students = $students;
        }
        //print_r($marked_student_array);exit;
        //make teacher create attendance for the day
        return response()->json(compact('students', 'present_students', 'absent_students', 'toDate', 'class_teacher_id', 'marked_student_array', 'attendance_id', 'no_of_days_in_month', 'day', 'day_of_week', 'date_in_words', 'month_and_year'));
    }
    public function createSubjectAttendance(Request $request)
    {
        $teacher = new Teacher();
        $class_attendance_obj = new ClassAttendance();
        $sess_id = $this->getSession()->id;
        $term_id = $this->getTerm()->id;
        $school_id = $this->getSchool()->id;
        $subject_teacher_id = $request->subject_teacher_id;

        //echo $option_id;exit;
        //$class_teacher_id = $option_id;

        $subject_teacher = SubjectTeacher::find($subject_teacher_id);
        $students = $teacher->teacherClassStudents($subject_teacher->class_teacher_id, $sess_id, $term_id, $school_id);
        //$fromDate = fromDate();
        $toDate = toDate();
        if ($request->date) {
            $toDate = $request->date;
        }
        $day = (int) (date('d', strtotime($toDate)));
        $month = date('m', strtotime($toDate));
        $year = date('Y', strtotime($toDate));

        $day_of_week = date('l', strtotime($toDate));
        $date_in_words = 'Take Attendance for ' . getDateFormatWords($toDate);
        $month_and_year = 'Attendance for ' . date('F, Y', strtotime($toDate));
        $no_of_days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);



        $options = array(
            'option' => 'subject', //$option,
            //'fromDate'=>$fromDate,
            'toDate' => $toDate,
            'id' => $subject_teacher_id
        );

        list($marked_month_attendances, $marked_today, $marked_student_array, $attendance_id) = $class_attendance_obj->markedAttendance($options);

        $absent_students = [];
        $present_students = [];
        if (isset($marked_student_array[$day])) {
            foreach ($students as $student) {


                if (in_array($student->id, $marked_student_array[$day])) {
                    $present_students[] = $student;
                } else {
                    $absent_students[] = $student;
                }
            }
        } else {
            $absent_students = $students;
        }
        //print_r($marked_student_array);exit;
        //make teacher create attendance for the day
        return response()->json(compact('students', 'present_students', 'absent_students', 'toDate', 'subject_teacher_id', 'marked_student_array', 'attendance_id', 'no_of_days_in_month', 'day', 'day_of_week', 'date_in_words', 'month_and_year'));
    }



    public function storeClassAttendance(Request $request)
    {
        $teacher = new Teacher();
        $sess_id = $this->getSession()->id;
        $term_id = $this->getTerm()->id;
        $school_id = $this->getSchool()->id;

        $class_teacher_id = $request->class_teacher_id;
        $present_students = $request->present_students;
        $absent_students = $request->absent_students;
        //fetch all students in class
        //$students = $teacher->teacherClassStudents($class_teacher_id,$sess_id,$term_id,$school_id);

        ////////////////////////////////////Get absent students//////////////////////////////////
        $present_students_array = [];
        $absent_students_array = [];
        foreach ($absent_students as $student) {

            $absent_students_array[] = $student['id'];
        }
        $absent_students = implode('~', array_unique($absent_students_array));
        ////////////////////////////////////Get absent students//////////////////////////////////
        foreach ($present_students as $student) {

            $present_students_array[] = $student['id'];
        }
        $student_ids = implode('~', array_unique($present_students_array));


        //if ( $request->option == 'class') {

        if ($request->attendance_id != null) {
            $class_attend = ClassAttendance::find($request->attendance_id);
        } else {
            $class_attend = new ClassAttendance();
        }


        $class_teacher = ClassTeacher::find($class_teacher_id);

        $class_attend->school_id = $school_id;

        $class_attend->class_teacher_id = $class_teacher_id;
        $class_attend->level_id = $class_teacher->level_id;

        $class_attend->student_ids = $student_ids;

        $class_attend->absent_students = $absent_students;
        $class_attend->total_present = count($present_students_array);
        $class_attend->total_absent = count($absent_students_array);

        $class_attend->sess_id = $sess_id;

        $class_attend->term_id = $term_id;

        $class_attend->date = date('Y-m-d', strtotime($request->date));

        $class_attend->save();

        //$request->subject = 'ClassAttendance';



        $action = "marked student attendance in " . $class_teacher->c_class->name . ' for ' . getDateFormatWords($request->date);
        /*}else {

            $sub_attend->subject_teacher_id = $request->option_id;

            $sub_attend->student_ids = $student_ids;

            $sub_attend->sess_id = $this->getSession()->id;

            $sub_attend->term_id = $this->getTerm()->id;

            $sub_attend->save();


            $request->subject_teacher_id = $request->option_id;

            $action = "Marked student attendance in ".str_replace('+', ' ', $request->class)." ".str_replace('+', ' ', $request->subject);
        }   */

        //log this activity
        $this->teacherStudentEventTrail($request, $action, 'class');

        return 'success';
        //return back()->withInput();
    }
    public function storeSubjectAttendance(Request $request)
    {
        $teacher = new Teacher();
        $sess_id = $this->getSession()->id;
        $term_id = $this->getTerm()->id;
        $school_id = $this->getSchool()->id;

        $subject_teacher_id = $request->subject_teacher_id;
        $present_students = $request->present_students;
        $absent_students = $request->absent_students;
        //fetch all students in class
        //$students = $teacher->teacherClassStudents($class_teacher_id,$sess_id,$term_id,$school_id);

        ////////////////////////////////////Get absent students//////////////////////////////////
        $present_students_array = [];
        $absent_students_array = [];
        foreach ($absent_students as $student) {

            $absent_students_array[] = $student['id'];
        }
        $absent_students = implode('~', array_unique($absent_students_array));
        ////////////////////////////////////Get absent students//////////////////////////////////
        foreach ($present_students as $student) {

            $present_students_array[] = $student['id'];
        }
        $student_ids = implode('~', array_unique($present_students_array));


        //if ( $request->option == 'class') {

        if ($request->attendance_id != null) {
            $sub_attend = SubjectAttendance::find($request->attendance_id);
        } else {
            $sub_attend = new SubjectAttendance();
        }


        $subject_teacher = SubjectTeacher::find($subject_teacher_id);

        $sub_attend->school_id = $school_id;

        $sub_attend->subject_teacher_id = $subject_teacher_id;

        $sub_attend->student_ids = $student_ids;

        $sub_attend->absent_students = $absent_students;
        $sub_attend->total_present = count($present_students_array);
        $sub_attend->total_absent = count($absent_students_array);

        $sub_attend->sess_id = $sess_id;

        $sub_attend->term_id = $term_id;

        $sub_attend->date = (isset($request->date) && $request->date !== '') ? date('Y-m-d', strtotime($request->date)) : date('Y-m-d', strtotime('now'));

        $sub_attend->save();

        //$request->subject = 'ClassAttendance';



        $action = "marked student attendance in " . $subject_teacher->subject->name . " (" . $subject_teacher->classTeacher->c_class->name . ") for " . getDateFormatWords($request->date);
        /*}else {

            $sub_attend->subject_teacher_id = $request->option_id;

            $sub_attend->student_ids = $student_ids;

            $sub_attend->sess_id = $this->getSession()->id;

            $sub_attend->term_id = $this->getTerm()->id;

            $sub_attend->save();


            $request->subject_teacher_id = $request->option_id;

            $action = "Marked student attendance in ".str_replace('+', ' ', $request->class)." ".str_replace('+', ' ', $request->subject);
        }   */

        //log this activity
        $this->teacherStudentEventTrail($request, $action, 'subject');

        return 'success';
        //return back()->withInput();
    }

    /*public function update( Request $request, $id)
    {
        $class_attendance_obj = new ClassAttendance();

        $student_id = $request->student_id;

        $option = $request->option;

        $fromDate = fromDate();

        $toDate = toDate();

        $options = array(
                            'option'=>$option,
                            'fromDate'=>$fromDate,
                            'toDate'=>$toDate,
                            'id'=>$request->option_id
                        );

        $marked_students = $class_attendance_obj->markedAttendance($options);

        $student_ids = $marked_students->student_ids;
        if($request->action == 'mark_absent') {
            $remaining_students = deleteSingleElementFromString($student_ids, $student_id);
        }else {
            $remaining_students = addSingleElementToString($student_ids, $student_id);
        }


       if ( $option == 'class') {

            $class_atend = ClassAttendance::findOrFail($id);



            $inputs['student_ids'] = $remaining_students;

            $class_atend->update($inputs);

            $request->subject = 'ClassAttendance';
            if ($remaining_students == "") {
                $class_atend->delete(); //delete the row if students are empty
            }

        }else {
            $subject_atend =SubjectAttendance::findOrFail($id);

            $inputs['student_ids'] = $remaining_students;

            $subject_atend->update($inputs);

            if ($remaining_students == "") {
                $subject_atend->delete();//delete the row if students are empty
            }
        }




        return redirect()->route('create_attendance',
                            [
                                'option'=>$request->option,
                                'class'=>$request->class,
                                'id'=> $request->option_id,
                                'subject'=>$request->subject

                            ]);
    }*/

    public function getStudents($class_id)
    {
        $students = Student::where('class_id', $class_id)->get();
        $html = "";
        foreach ($students as $student) {
            $html .= "<tr>" .
                "<td>" . $student->id . "</td>" .
                "<td>" . $student->first_name . ' ' . $student->last_name . "</td>" .
                "<td>" .
                "<input type='radio' name='attendance[" . $student->id . "]' value='1'> Present " .
                "<input type='radio' name='attendance[" . $student->id . "]' value='0'> Absent " .
                "</td>" .
                "</tr>";
        }
        return $html;
    }

    public function fetchLevelAttendanceChart(Request $request)
    {
        $date = getDateFormat($request->date);
        if (isset($request->school_id)) {
            $school_id = $request->school_id;
        } else {
            $school_id = $this->getSchool()->id;
        }
        $school_name = School::find($school_id)->name;
        $heading_text = 'Level Attendance Chart for ' . getDateFormatWords($date);
        $levels = $this->getLevels();
        $presentData = [];
        $absentData = [];
        $chartLabel = [];
        foreach ($levels as $level) {
            $total_present = ClassAttendance::where(['level_id' => $level->id, 'school_id' => $school_id])
                ->whereDate('date', '=', $date)->sum('total_present');
            $total_absent = ClassAttendance::where(['level_id' => $level->id, 'school_id' => $school_id])
                ->whereDate('date', '=', $date)->sum('total_absent');

            $presentData[] = $total_present;
            $absentData[] = $total_absent;
            $chartLabel[] = $level->level;
        }

        return response()->json(compact('chartLabel', 'presentData', 'absentData', 'heading_text', 'school_name'), 200);
    }
    public function loadAttendanceChart(Request $request)
    {
        $class_attendance = new ClassAttendance();
        $date = getDateFormat($request->date);

        $options = [];
        if ($request->option == 'class') {
            $class_attendances = $class_attendance->fetchAttendanceReport($this->getSchool()->id, $date, $this->getStaff()->id);

            foreach ($class_attendances as $class_attendance) :

                $class = $class_attendance->c_class->name;
                $count = $class_attendance->attendance_count;

                $options[] =  array(
                    'name' => $class,
                    'count' => $count
                );
            endforeach;
        } else {
        }

        return $attendance =  response()->json([
            'attendance' => $options
        ]);
    }
}
