<?php

namespace Modules\Core\Http\Controllers;

use App\Models\ClassAttendance;
use App\Models\ClassTeacher;
use App\Models\SubjectTeacher;
use App\Models\StudentsInClass;
use App\Models\ResultAction;
use App\Models\Result;
use App\Models\Level;
use App\Models\Student;
use App\Models\SSession;
use App\Models\CClass;
use App\Models\Teacher;
use App\Models\Grade;
use App\Models\IncomeAndExpense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class ReportsController extends Controller
{
    //

    public function index()
    {
        $request = request()->all();
        if (isset($request['account']) && $request['account'] == "1") {
            $report_category = [
                "finance" => 'Financial Report'
            ];
        } elseif ($this->getUser()->hasRole('admin')) {
            $report_category = [
                "" => 'Select Report to View',
                "admission" => 'Admission Report',
                "attendance" => 'Attendance Report',
                "finance" => 'Financial Report',
                "student_performance" => "Students' Performance",
            ];
        } else {
            return $this->render('errors.403');
        }

        return $this->render('core::reports.index', compact('report_category'));
    }




    public function displayReportChart(Request $request)
    {
        if (isset($request->category) && $request->category != "") {
            $category = $request->category;

            switch ($category) {
                case 'admission':
                    return $this->admissionReport();
                    break;
                case 'attendance':
                    return $this->attendanceReport();
                    break;
                case 'finance':
                    return $this->financialReport();
                    break;
                case 'student_performance':
                    return $this->studentPerformanceReport();
                    break;
                case 'analyse_class_result':
                    return $this->analyseClassResult();
                    break;
                case 'subject_averages':
                    return $this->subjectAverages();
                    break;
                case 'student_academic':
                    $id = $request->student_id;
                    return $this->studentAcademicReport($id);
                    break;
                case 'student_attendance':
                    $id = $request->student_id;
                    return $this->studentAttendanceReport($id);
                    break;


                default:
                    # code...
                    break;
            }
        }
        return $this->render('errors.404');
    }



    public function admissionReport()
    {
        $request = request()->all();

        $school_id = $this->getSchool()->id;
        $sess_id = $this->getSession()->id;
        $term_id = $this->getTerm()->id;

        $levels = $this->getLevels(); //Level::orderBy('level', 'ASC')->get();
        $all_sessions = SSession::get();

        if (isset($request['admission_sess_id']) && $request['admission_sess_id'] != "") {

            $admission_sess_id = $request['admission_sess_id'];
        } else {
            $admission_sess_id = $sess_id;
        }

        $selected_session = SSession::find($admission_sess_id);

        $chart_only = "false";
        if (isset($request['chart_only'])) {
            $chart_only = "true";
        }



        foreach ($levels as $level) {
            $level->total_admission_count = Student::where(['school_id' => $school_id, 'admission_sess_id' => $admission_sess_id, 'level_admitted' => $level->id])->count();
            $level->male_admission_count = Student::join('users', 'users.id', 'students.user_id')
                ->where(['school_id' => $school_id, 'gender' => 'male', 'admission_sess_id' => $admission_sess_id, 'level_admitted' => $level->id])->count();
            $level->female_admission_count = Student::join('users', 'users.id', 'students.user_id')
                ->where(['school_id' => $school_id, 'gender' => 'female', 'admission_sess_id' => $admission_sess_id, 'level_admitted' => $level->id])->count();
        }


        return $this->render('core::reports.admission', compact('levels', 'selected_session', 'all_sessions', 'admission_sess_id', 'chart_only'));
    }




    public function attendanceReport()
    {
        $class_attendance_obj = new ClassAttendance();
        $teacher_obj = new Teacher();
        $request = request()->all();
        $school_id = $this->getSchool()->id;
        $sess_id = $this->getSession()->id;
        $term_id = $this->getTerm()->id;

        $date = date('Y-m', strtotime(todayDate())); //today

        $all_classes = ClassTeacher::where('school_id', $school_id)->orderBy('id')->get();
        $attendance_class_id = $all_classes[0]->id;

        $chart_only = "false";
        if (isset($request['chart_only'])) {
            $chart_only = "true";
        }

        if (isset($request['date'], $request['attendance_class_id']) && $request['date'] != "" && $request['attendance_class_id'] != "") {

            $date = $request['date'];
            $attendance_class_id = $request['attendance_class_id'];
        }
        $day = (int)date('d', strtotime($date));
        $month = date('m', strtotime($date));
        $year = date('Y', strtotime($date));

        $no_of_days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);

        $options = array(
            'option' => 'class', //$option,
            'toDate' => $date,
            'id' => $attendance_class_id
        );

        list($marked_month_attendances, $marked_today, $marked_student_array, $attendance_id) = $class_attendance_obj->markedAttendance($options);

        //fetch all students in class
        list($student_ids_arr, $students) = $teacher_obj->teacherClassStudents($attendance_class_id, $sess_id, $term_id, $school_id);

        $attendances =  $marked_month_attendances;
        $total_present = 0;
        $total_absent = 0;
        $total_students = count($student_ids_arr);


        if ($attendances != '[]') {
            foreach ($attendances as $attendance) {

                $present_students = 0;
                $absent_students = 0;
                if ($attendance->student_ids != null && $attendance->student_ids != "") {
                    $present_students = count(explode('~', $attendance->student_ids));
                }

                if ($attendance->absent_students != null && $attendance->absent_students != "") {
                    $absent_students = count(explode('~', $attendance->absent_students));
                }


                //$total_students = $present_students + $absent_students;


                $attendance->present_students = $present_students;
                $attendance->absent_students = $absent_students;
                $attendance->total_students = $total_students;
                $attendance->average = $total_students / 2;

                $total_present += $present_students;
                $total_absent += $absent_students;
            }
        }
        $percentage_present = 0;
        $percentage_absent = 0;
        if ($total_students != 0) {
            $percentage_present = $total_present / $total_students * 100;
            $percentage_absent = $total_absent / $total_students * 100;
        }

        $class = ClassTeacher::find($attendance_class_id)->c_class;



        return $this->render('core::reports.attendance', compact('attendances', 'class', 'percentage_present', 'percentage_absent', 'all_classes', 'no_of_days_in_month', 'date', 'attendance_class_id', 'students', 'marked_student_array', 'day', 'chart_only'));
    }




    public function financialReport()
    {
        $request = request()->all();

        $school_id = $this->getSchool()->id;
        $sess_id = $this->getSession()->id;
        $term_id = $this->getTerm()->id;

        $chart_only = "false";
        if (isset($request['chart_only'])) {
            $chart_only = "true";
        }

        $date = date('Y-m', strtotime(todayDate())); //today
        if (isset($request['date']) && $request['date'] != "") {

            $date = $request['date'];
        }
        $day = (int)(date('d', strtotime($date)));
        $month_int = date('m', strtotime($date));
        $year = (int) (date('Y', strtotime($date)));

        $no_of_days_in_month = cal_days_in_month(CAL_GREGORIAN, $month_int, $year);

        $month_str = date('F', strtotime($date));

        $income_n_expenses = IncomeAndExpense::selectRaw('SUM(amount) as sum, status, DATE(created_at) as date')
            ->groupBy('date', 'status')
            ->where(['school_id' => $school_id, 'pay_month' => $month_str, 'pay_year' => $year])
            ->get();



        $total_income = 0;
        $total_expenses = 0;
        foreach ($income_n_expenses as $income_n_expense) {


            if ($income_n_expense->status == 'income') {
                $income_n_expense->income = $income_n_expense->sum;
                $total_income += $income_n_expense->sum;
            } else if ($income_n_expense->status == 'expenses') {
                $income_n_expense->expenditure = $income_n_expense->sum;
                $total_expenses += $income_n_expense->sum;
            }
        }
        $total_money = $total_income + $total_expenses;


        return $this->render('core::reports.finance', compact('income_n_expenses', 'date', 'total_income', 'total_expenses', 'no_of_days_in_month', 'chart_only'));
    }




    public function studentPerformanceReport()
    {
        $request = request()->all();

        $school_id = $this->getSchool()->id;
        $sess_id = $this->getSession()->id;
        $term_id = $this->getTerm()->id;


        $chart_only = "false";
        if (isset($request['chart_only'])) {
            $chart_only = "true";
        }

        $all_sessions = SSession::get();

        $all_classes = ClassTeacher::where('school_id', $school_id)->orderBy('id')->get();
        if ($all_classes->isEmpty()) {
            return "Please configure classes";
        }
        $class_teacher_id = $all_classes[0]->id;
        $hide_selection = '0';
        if (isset($request['sess_id'], $request['class_teacher_id'], $request['term_id']) && $request['sess_id'] != "" && $request['class_teacher_id'] != "" && $request['term_id'] != "") {

            $sess_id = $request['sess_id'];
            $class_teacher_id = $request['class_teacher_id'];
            $term_id = $request['term_id'];
        }
        if (isset($request['hide_selection'])) {
            $hide_selection = $request['hide_selection'];
        }

        $class_details = ClassTeacher::find($class_teacher_id);
        $curriculum_level_group_id = $class_details->level->levelGroup->id;
        $grades = $this->getLevelGrades($curriculum_level_group_id);
        //$grades = $this->getGrades();


        $subject_teachers = SubjectTeacher::where('class_teacher_id', $class_teacher_id)->get();

        $performance = [];
        $performance_color = [];
        foreach ($grades as $grade) :
            $performance[$grade->grade] = [];
            $performance_color[$grade->grade] = $grade->color_code;
        //will output something like:
        //
        /*  'A1'=>[],
                'B2'=>[],
                'B3'=>[],
                'C4'=>[],
                'C5'=>[],
                'C6'=>[],
                'D7'=>[],
                'E8'=>[],
                'F9'=>[],*/
        endforeach;




        foreach ($subject_teachers as $subject_teacher) :

            if ($term_id == 0) {
                $results = Result::where(['school_id' => $school_id, 'sess_id' => $sess_id, 'subject_teacher_id' => $subject_teacher->id])->where('comments', "!=", NULL)->get();
            } else {
                $results = Result::where(['school_id' => $school_id, 'sess_id' => $sess_id, 'term_id' => $term_id, 'subject_teacher_id' => $subject_teacher->id])->where('comments', "!=", NULL)->get();
            }

            $result = new Result();

            list($subject_class_average, $subject_highest_score, $subject_lowest_score, $male_average, $female_average) = $result->subjectStudentPerformance($results);

            $subject_teacher->subject_result_details = $results;
            $subject_teacher->subject_class_average = sprintf("%01.1f", $subject_class_average);
            $subject_teacher->subject_highest_score = $subject_highest_score;
            $subject_teacher->subject_lowest_score = $subject_lowest_score;
            $subject_teacher->male_average = sprintf("%01.1f", $male_average);
            $subject_teacher->female_average = sprintf("%01.1f", $female_average);

            $count_grades = count($grades);

            for ($i = 0; $i < $count_grades; $i++) {
                # code...
                $grades[$i]->grade_count = 0;

                //will output initial values like:
                //$A1 = 0;$B2 = 0;$B3 = 0;$C4 = 0;$C5 = 0;$C6 = 0;$D7 = 0;$E8 = 0;$F9 = 0;
            }

            foreach ($results as $result) {
                $score = $result->total;

                $j = 0;
                foreach ($grades as $grade) {
                    //we do a check to get grade count like:

                    /*if($score <= 100 && $score >= 75){
                        $A1++;
                    }elseif ($score <= 74 && $score >= 70) {
                        $B2++;
                    }elseif ($score <= 69 && $score >= 65) {
                        $B3++;
                    }elseif ($score <= 64 && $score >= 60) {
                        $C4++;
                    }elseif ($score <= 59 && $score >= 55) {
                        $C5++;
                    }elseif ($score <= 54 && $score >= 50) {
                        $C6++;
                    }elseif ($score <= 49 && $score >= 45) {
                        $D7++;
                    }elseif ($score <= 44 && $score >= 40) {
                        $E8++;
                    }else{
                        $F9++;
                    }*/ ////////////////////////////////////////////////////

                    if ($score <= $grade->upper_limit && $score >= $grade->lower_limit) {

                        $grades[$j]->grade_count++;
                        //will ouput something like:
                        //$A1++;
                    }

                    $j++;
                }
            }
            //print_r( $performance);

            for ($i = 0; $i < $count_grades; $i++) {
                # code...
                $performance[$grades[$i]->grade] = $grades[$i]->grade_count;

                //will output something like:

                /*$performance['A1'][] = $A1; //where $A1 == 3, 5, etc any number
                $performance['B2'][] = $B2;
                $performance['B3'][] = $B3;
                $performance['C4'][] = $C4;
                $performance['C5'][] = $C5;
                $performance['C6'][] = $C6;
                $performance['D7'][] = $D7;
                $performance['E8'][] = $E8;
                $performance['F9'][] = $F9;*/
            }
            $subject_teacher->performance = $performance;

        endforeach;



        $term_array = [
            '1' => 'First Term',
            '2' => 'Second Term',
            '3' => 'Third Term',
            '0' => 'All Terms',

        ];
        $selected_session = SSession::find($sess_id)->name;
        $selected_class = ClassTeacher::find($class_teacher_id)->c_class->name;
        $selected_term = $term_array[$term_id];


        return $this->render('core::reports.students_performance', compact('subject_teachers', 'all_sessions', 'all_classes', 'term_array', 'class_teacher_id', 'sess_id', 'term_id', 'performance', 'performance_color', 'selected_session', 'selected_class', 'selected_term', 'chart_only', 'hide_selection'));
    }

    /*public function studentAttendance()
    {
        $class_attendance_obj = new ClassAttendance();
        $teacher_obj = new Teacher();
        $request = request()->all();
        $school_id = $this->getSchool()->id;
        $sess_id = $this->getSession()->id;
        $term_id = $this->getTerm()->id;

        $date = date('Y-m', strtotime(todayDate()));//today

        //$attendance_class_id =

        $chart_only = "false";
        if(isset($request['chart_only'])){
            $chart_only = "true";
        }

        if (isset($request['date'], $request['attendance_class_id'], $request['student_id']) && $request['date'] != "" && $request['attendance_class_id'] != "" && $request['student_id'] != "") {

            $date = $request['date'];
            $attendance_class_id = $request['attendance_class_id'];
            $student_id = $request['student_id'];
        }
        $day = (int)date('d', strtotime($date));
        $month = date('m', strtotime($date));
        $year = date('Y', strtotime($date));

        $no_of_days_in_month = cal_days_in_month(CAL_GREGORIAN,$month,$year);

        $options = array(
                            'option'=>'class',//$option,
                            'toDate'=>$date,
                            'id'=>$attendance_class_id
                        );

        list($marked_month_attendances,$marked_today,$marked_student_array, $attendance_id) = $class_attendance_obj->markedAttendance($options);

        //fetch all students in class
        list($student_ids_arr,$students) = $teacher_obj->teacherClassStudents($attendance_class_id,$sess_id,$term_id,$school_id);

        $attendances =  $marked_month_attendances;
        $total_present = 0;
        $total_absent = 0;
        $total_students = count($student_ids_arr);


        if (!$attendances->isEmpty()) {
            foreach ($attendances as $attendance) {

                $present_students = 0;
                $absent_students = 0;
                if ($attendance->student_ids != null && $attendance->student_ids != "") {
                    $present_students = count(explode('~', $attendance->student_ids));
                }

                if ($attendance->absent_students != null && $attendance->absent_students != "") {
                    $absent_students = count(explode('~', $attendance->absent_students));
                }


                //$total_students = $present_students + $absent_students;


                $attendance->present_students = $present_students;
                $attendance->absent_students = $absent_students;
                $attendance->total_students = $total_students;
                $attendance->average = $total_students/2;

                $total_present += $present_students;
                $total_absent += $absent_students;



            }
        }
        $percentage_present = 0;
        $percentage_absent = 0;
        if ($total_students != 0) {
            $percentage_present = $total_present/$total_students * 100;
            $percentage_absent = $total_absent/$total_students * 100;
        }

        $class = ClassTeacher::find($attendance_class_id)->c_class;



        return $this->render('core::reports.attendance', compact('attendances', 'class', 'percentage_present', 'percentage_absent', 'all_classes', 'no_of_days_in_month', 'date', 'attendance_class_id', 'students', 'marked_student_array', 'day', 'chart_only'));
    }*/

    public function parentViewStudentAcademicChart(Request $request, Result $result)
    {
        $user = $this->getUser();
        $request = request()->all();
        $all_sessions = SSession::get();
        $school_id = $this->getSchool()->id;
        $sess_id = $this->getSession()->id;
        if (isset($request['sess_id']) && $request['sess_id'] != "") {

            $sess_id = $request['sess_id'];
        }
        $selected_session = SSession::find($sess_id)->name;
        //$grades = $this->getGrades();//Grade::all();
        //check whether the user has parent role
        if ($user->hasRole('parent')) {

            $guardian = $this->getGuardian();
            $ward_ids = $guardian->ward_ids;

            $ward_id_array = explode('~', substr($ward_ids, 1));



            $student_average = [];
            foreach ($ward_id_array as $key => $student_id) :
                if ($student_id != "") {
                    # code...
                    $term_id = '';
                    $student = Student::find($student_id);
                    $student_name = $student->user->first_name . ' ' . $student->user->last_name;
                    $student_current_class = $student_in_class_obj->fetchStudentInClass($student_id, $sess_id, $term_id, $school_id);
                    $student_termly_performance = null;
                    if ($student_current_class) {
                        $level_id = $student_current_class->classTeacher->level_id;
                        $level = Level::find($level_id);
                        $curriculum_level_group_id = $level->levelGroup->id;
                        $grades = $this->getLevelGrades($curriculum_level_group_id);
                        //$grades = $this->getGrades();
                        //fetch the academic detail of this student for the selected session
                        list($student_termly_performance, $class_termly_average) = $this->fetchStudentAcademicPerformance($student_id, $sess_id, $school_id, $grades);
                    }
                    $student_average[$student_name] = $student_termly_performance;
                }
            endforeach;


            //return $student_average;

            return $this->render('core::reports.parent_view_student_performance', compact('student_average', 'selected_session', 'all_sessions', 'sess_id'));
        }
    }

    public function studentAcademicReport($id)
    {
        if ($id != null) {
            $result = new Result();
            $student = Student::find($id);
            $student_id = $student->id;
            $request = request()->all();

            $all_sessions = SSession::get();
            $school_id = $this->getSchool()->id;
            $sess_id = $this->getSession()->id;
            $school_id = $this->getSchool()->id;


            if (isset($request['sess_id']) && $request['sess_id'] != "") {

                $sess_id = $request['sess_id'];
            }
            $selected_session = SSession::find($sess_id)->name;
            $level = Level::find($student->current_level);
            $curriculum_level_group_id = $level->levelGroup->id;
            $grades = $this->getLevelGrades($curriculum_level_group_id);
            //$grades = $this->getGrades();//Grade::all();

            $student_average = [];
            list($student_termly_performance, $class_termly_average) = $this->fetchStudentAcademicPerformance($student_id, $sess_id, $school_id, $grades);

            $student_name = $student->user->first_name . ' ' . $student->user->last_name;
            $student_average[$student_name] = $student_termly_performance;
            $student_average['Class Average'] = $class_termly_average;

            //return $student_average;

            return $this->render('core::reports.student_academic_report', compact('student_average', 'selected_session', 'all_sessions', 'sess_id', 'student', 'student_name'));
        } //endif
    } //end method


    private function fetchStudentAcademicPerformance($student_id, $sess_id, $school_id, $grades)
    {
        $result = new Result();

        $student_termly_performance = [];
        $class_termly_average = [];
        for ($term_id = 1; $term_id <= 3; $term_id++) :

            $student_in_class_obj = new StudentsInClass();
            $student_class = $student_in_class_obj->fetchStudentInClass($student_id, $sess_id, $term_id, $school_id);

            if ($student_class) {
                $class_teacher_id = $student_class->class_teacher_id;
                $class_details = ClassTeacher::find($class_teacher_id);
                $curriculum_level_group_id = $class_details->level->levelGroup->id;
                $student_results = Result::where(
                    [
                        'class_teacher_id' => $class_teacher_id,
                        'school_id' => $school_id,
                        'sess_id' => $sess_id,
                        'term_id' => $term_id,
                        'student_id' => $student_id
                    ]
                )->get();

                if (!$student_results->isEmpty()) {

                    $total_subject_class_average = 0;
                    $total_student_score = 0;
                    $result_count = 0;
                    foreach ($student_results as $student_result) :

                        $subject_teacher_id = $student_result->subject_teacher_id;
                        //$student_result->user = Student::find($student_result->student_id)->user;
                        $subject_teacher = SubjectTeacher::find($subject_teacher_id);


                        $action_term = 'actions_term_' . $term_id;

                        $result_action = ResultAction::where(['sess_id' => $sess_id, 'school_id' => $school_id, 'subject_teacher_id' => $subject_teacher_id])->first();

                        $student_result->result_action_array = $result->resultStatusAction($result_action->$action_term);
                        //$total_for_avg = $total_for_avg+$student_result->total;
                        list($test, $total, $result_grade, $color, $grade_point, $interpretation) = $result->processResultInfo($student_result, $grades, 'full', $curriculum_level_group_id);


                        //fetch the performance of students for each subject in this class
                        $subject_result_details = Result::where([
                            'subject_teacher_id' => $subject_teacher_id,
                            'school_id' => $school_id,
                            'sess_id' => $sess_id,
                            'term_id' => $term_id
                        ])->get();

                        list($subject_class_average, $subject_highest_score, $subject_lowest_score, $male_average, $female_average) = $result->subjectStudentPerformance($subject_result_details);

                        $student_result->test = $test;
                        $student_result->result_grade = $result_grade;
                        $student_result->color = $color;
                        $student_result->grade_point = $grade_point;

                        $student_result->subject_class_average = $subject_class_average;
                        $student_result->subject_highest_score = $subject_highest_score;
                        $student_result->subject_lowest_score = $subject_lowest_score;
                        if ($total != null) {
                            $result_count++;
                            $total_subject_class_average += $subject_class_average;
                            $total_student_score += $total;
                        }

                    endforeach;


                    if ($result_count == 0) {
                        $class_termly_average['term_' . $term_id] = 0;
                        $student_termly_performance['term_' . $term_id] = 0;
                    } else {
                        $class_termly_average['term_' . $term_id] = $total_subject_class_average / $result_count;
                        $student_termly_performance['term_' . $term_id] = $total_student_score / $result_count;
                    }
                };
            } else {
                $class_termly_average['term_' . $term_id] = 0;
                $student_termly_performance['term_' . $term_id] = 0;
            }


        endfor;



        return array($student_termly_performance, $class_termly_average);
    }

    public function studentAttendanceReport($id)
    {
        $class_attendance_obj = new ClassAttendance();
        $teacher_obj = new Teacher();
        $request = request()->all();
        $school_id = $this->getSchool()->id;
        $sess_id = $this->getSession()->id;
        $term_id = $this->getTerm()->id;
        $student = Student::find($id);
        $student_id = $id;
        $date = date('Y-m', strtotime(todayDate())); //today

        //$all_classes = ClassTeacher::where('school_id', $school_id)->orderBy('id')->get();


        $chart_only = "false";
        if (isset($request['chart_only'])) {
            $chart_only = "true";
        }

        if (isset($request['date']) && $request['date']) {

            $date = $request['date'];
        }

        if (isset($request['attendance_class_id']) && $request['attendance_class_id'] != "") {

            $attendance_class_id = $request['attendance_class_id'];
        }
        $day = (int)date('d', strtotime($date));
        $month = date('m', strtotime($date));
        $year = date('Y', strtotime($date));

        $no_of_days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);

        $options = array(
            'option' => 'class', //$option,
            'toDate' => $date,
            'id' => $attendance_class_id
        );

        list($marked_month_attendances, $marked_today, $marked_student_array, $attendance_id) = $class_attendance_obj->markedAttendance($options);

        //fetch all students in class
        list($student_ids_arr, $students) = $teacher_obj->teacherClassStudents($attendance_class_id, $sess_id, $term_id, $school_id);

        $attendances =  $marked_month_attendances;
        $total_present = 0;
        $total_absent = 0;

        if ($attendances != '[]') {
            foreach ($attendances as $attendance) :

                $present = 0;
                $absent = 0;
                if ($attendance->student_ids != null && $attendance->student_ids != "") {


                    $present_students_array = explode('~', $attendance->student_ids);

                    if (in_array($student_id, $present_students_array)) {
                        $present = 1;
                        $total_present++;
                    } else {
                        $absent = 1;
                        $total_absent++;
                    }
                }

                $attendance->present = $present;
                $attendance->absent = $absent;




            endforeach;
        }

        $total_attendance = $total_present + $total_absent;
        $percentage_present = 0;
        $percentage_absent = 0;
        if ($total_attendance != 0) {
            $percentage_present = $total_present / $total_attendance * 100;
            $percentage_absent = $total_absent / $total_attendance * 100;
        }

        $class = ClassTeacher::find($attendance_class_id)->c_class;



        return $this->render(compact('attendances', 'class', 'percentage_present', 'percentage_absent', 'no_of_days_in_month', 'date', 'attendance_class_id', 'student', 'marked_student_array', 'day', 'chart_only'));
    }

    public function analyseClassResult()
    {
        $request = request()->all();

        $school_id = $this->getSchool()->id;
        $sess_id = $this->getSession()->id;
        $term_id = $this->getTerm()->id;

        //$grades = $this->getGrades();
        $chart_only = "false";
        if (isset($request['chart_only'])) {
            $chart_only = "true";
        }

        $all_sessions = SSession::get();

        $all_classes = ClassTeacher::where('school_id', $school_id)->orderBy('id')->get();
        $class_teacher_id = $all_classes[0]->id;
        $hide_selection = '0';
        if (isset($request['sess_id'], $request['class_teacher_id'], $request['term_id']) && $request['sess_id'] != "" && $request['class_teacher_id'] != "" && $request['term_id'] != "") {

            $sess_id = $request['sess_id'];
            $class_teacher_id = $request['class_teacher_id'];
            $term_id = $request['term_id'];
        }
        if (isset($request['hide_selection'])) {
            $hide_selection = $request['hide_selection'];
        }


        $class_details = ClassTeacher::find($class_teacher_id);
        $curriculum_level_group_id = $class_details->level->levelGroup->id;
        $grades = $this->getLevelGrades($curriculum_level_group_id);

        $subject_teachers = SubjectTeacher::where('class_teacher_id', $class_teacher_id)->get();

        $performance = [];
        $performance_color = [];
        foreach ($grades as $grade) :
            $performance[$grade->grade] = [];
            $performance_color[$grade->grade] = $grade->color_code;
        //will output something like:
        //
        /*  'A1'=>[],
                'B2'=>[],
                'B3'=>[],
                'C4'=>[],
                'C5'=>[],
                'C6'=>[],
                'D7'=>[],
                'E8'=>[],
                'F9'=>[],*/
        endforeach;




        foreach ($subject_teachers as $subject_teacher) :

            if ($term_id == 0) {
                $results = Result::where(['school_id' => $school_id, 'sess_id' => $sess_id, 'subject_teacher_id' => $subject_teacher->id])->where('comments', "!=", NULL)->get();
            } else {
                $results = Result::where(['school_id' => $school_id, 'sess_id' => $sess_id, 'term_id' => $term_id, 'subject_teacher_id' => $subject_teacher->id])->where('comments', "!=", NULL)->get();
            }

            $result = new Result();

            list($subject_class_average, $subject_highest_score, $subject_lowest_score, $male_average, $female_average) = $result->subjectStudentPerformance($results);

            $subject_teacher->subject_result_details = $results;
            $subject_teacher->subject_class_average = sprintf("%01.1f", $subject_class_average);
            $subject_teacher->subject_highest_score = $subject_highest_score;
            $subject_teacher->subject_lowest_score = $subject_lowest_score;
            $subject_teacher->male_average = sprintf("%01.1f", $male_average);
            $subject_teacher->female_average = sprintf("%01.1f", $female_average);

            $count_grades = count($grades);

            for ($i = 0; $i < $count_grades; $i++) {
                # code...
                $grades[$i]->grade_count = 0;

                //will output initial values like:
                //$A1 = 0;$B2 = 0;$B3 = 0;$C4 = 0;$C5 = 0;$C6 = 0;$D7 = 0;$E8 = 0;$F9 = 0;
            }

            foreach ($results as $result) {
                $score = $result->total;

                $j = 0;
                foreach ($grades as $grade) {
                    //we do a check to get grade count like:

                    /*if($score <= 100 && $score >= 75){
                        $A1++;
                    }elseif ($score <= 74 && $score >= 70) {
                        $B2++;
                    }elseif ($score <= 69 && $score >= 65) {
                        $B3++;
                    }elseif ($score <= 64 && $score >= 60) {
                        $C4++;
                    }elseif ($score <= 59 && $score >= 55) {
                        $C5++;
                    }elseif ($score <= 54 && $score >= 50) {
                        $C6++;
                    }elseif ($score <= 49 && $score >= 45) {
                        $D7++;
                    }elseif ($score <= 44 && $score >= 40) {
                        $E8++;
                    }else{
                        $F9++;
                    }*/ ////////////////////////////////////////////////////

                    if ($score <= $grade->upper_limit && $score >= $grade->lower_limit) {

                        $grades[$j]->grade_count++;
                        //will ouput something like:
                        //$A1++;
                    }

                    $j++;
                }
            }
            //print_r( $performance);

            for ($i = 0; $i < $count_grades; $i++) {
                # code...
                $performance[$grades[$i]->grade] = $grades[$i]->grade_count;

                //will output something like:

                /*$performance['A1'][] = $A1; //where $A1 == 3, 5, etc any number
                $performance['B2'][] = $B2;
                $performance['B3'][] = $B3;
                $performance['C4'][] = $C4;
                $performance['C5'][] = $C5;
                $performance['C6'][] = $C6;
                $performance['D7'][] = $D7;
                $performance['E8'][] = $E8;
                $performance['F9'][] = $F9;*/
            }
            $subject_teacher->performance = $performance;

        endforeach;



        $term_array = [
            '1' => 'First Term',
            '2' => 'Second Term',
            '3' => 'Third Term',
            '0' => 'All Terms',

        ];

        $level_array = ['' => 'Select Level'];

        $levels = $this->getLevels();
        foreach ($levels as $level) {
            $level_array[$level->id] = $level->level;
        }
        $selected_session = SSession::find($sess_id)->name;
        $selected_class = ClassTeacher::find($class_teacher_id)->c_class->name;
        $selected_term = $term_array[$term_id];


        return $this->render('core::reports.academic_report_analysis', compact('subject_teachers', 'all_sessions', 'all_classes', 'term_array', 'class_teacher_id', 'sess_id', 'term_id', 'performance', 'performance_color', 'selected_session', 'selected_class', 'selected_term', 'chart_only', 'hide_selection', 'level_array'));

        //return $this->render('core::reports.academic_report_analysis', compact('subject_teachers', 'hide_selection', 'selected_session', 'selected_class', 'selected_term'));

    }

    public function subjectAverages()
    {
    }
}
