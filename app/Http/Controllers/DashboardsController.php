<?php

namespace App\Http\Controllers;

use App\Models\Alumni;
use App\Models\Grade;
use App\Models\Guardian;
use App\Models\Medical;
use App\Models\Remark;
use App\Models\Result;
use App\Models\Routine;
use App\Models\School;
use App\Models\PotentialSchool;
use App\Models\Student;
use App\Models\StudentsInClass;
use App\Models\ClassTeacher;
use App\Models\SubjectTeacher;
use App\Models\ClassActivity;
use App\Models\Teacher;
use App\Models\Term;
use App\Models\User;
use App\Models\Staff;
use App\Models\ClassAttendance;
use App\Models\AuditTrail;
use App\Models\News;
use App\Models\Event;
use App\Models\GroupOfSchool;
use App\Models\PartnerSchool;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Laracasts\Flash\Flash;

class DashboardsController extends Controller
{
    /**
     * This manages privileges based on roles
     *
     * @return \Illuminate\Http\Response
     */

    public function accessDenied()
    {
        return $this->render('errors.403');
    }

    public function navbarNotificationClicked()
    {
        session(['navbar_clicked' => 'true']);
    }

    public function navbarNotification()
    {
        $user = $this->getUser();
        $today = Carbon::now();
        $school_id = $this->getSchool()->id;
        $sess_id = $this->getSession()->id;
        $term_id = $this->getTerm()->id;

        $user_activities = $user->activityLog()->where('created_at', '>', $today->startOfWeek())->orderBy('id', 'DESC')->get();

        $activities = collect($user_activities);
        if ($user->hasRole('teacher')) {
            $staff = $this->getStaff();

            $class_activities = [];
            $class_teachers =  ClassTeacher::where(['school_id' => $school_id, 'teacher_id' => $staff->id])->get();
            foreach ($class_teachers as $class_teacher) {
                $class_activities = $class_teacher->classActivity()->orderBy('id', "DESC")->get();
                $activities = $activities->merge($class_activities);
            }
        }
        if ($user->hasRole('parent')) {
            # code...
            $guardian  = $this->getGuardian();
            $ward_ids = $guardian->ward_ids;

            $ward_id_array = explode('~', substr($ward_ids, 1));

            $class_teacher_id_array = [];


            foreach ($ward_id_array as $key => $student_id) :
                //make sure the student_id does not have an empty value
                if ($student_id != "") {

                    $student_in_class_obj = new StudentsInClass();
                    $student_class = $student_in_class_obj->fetchStudentInClass($student_id, $sess_id, $term_id, $school_id);

                    $class_teacher_id = $student_class->class_teacher_id;

                    //make class_teacher_id_array unique to avoid duplicate activity display from siblings of a parent in the same class
                    if (!in_array($class_teacher_id, $class_teacher_id_array)) {
                        //get the recent class activity for the week
                        $class_activities = ClassActivity::where(['class_teacher_id' => $class_teacher_id, 'school_id' => $school_id])->where('created_at', '>', $today->startOfWeek())->orderBy('id', 'DESC')->get();

                        $activities = $activities->merge($class_activities);
                    }
                    $class_teacher_id_array[] = $class_teacher_id;
                }

            endforeach;
        }
        $activities = $activities->sortByDesc('created_at');

        $notifications = News::where('school_id', $school_id)
            ->where('targeted_audience', 'like', $user->role)
            ->orWhere('targeted_audience', 'like', $user->role . '~%')
            ->orWhere('targeted_audience', 'like', '%~' . $user->role . '~%')
            ->orWhere('targeted_audience', 'like', '%~' . $user->role)
            ->orderBy('id', 'DESC')->get();

        if (!$notifications->isEmpty()) {
            foreach ($notifications as $notification) {

                $seen_by_array  = explode('~', $notification->seen_by);

                $notification->seen_by_array = $seen_by_array;
            }
        }
        $count_value = 0;
        if (session()->exists('count_notification')) {
            $count_value = session('count_notification');
        }
        $count = 0;

        foreach ($activities as $activity) {
            if ($activity->actor_id != $user->id) {
                $count++;
            }
        }
        if ($count > $count_value) {
            session(['count_notification' => ($count - $count_value)]);
            session()->forget(['navbar_clicked']);
            $count_value = session('count_notification');
        }

        if (session()->exists('navbar_clicked')) {
            $count_value = "";
        }

        return view('news.navbar_notification', compact('activities', 'user', 'notifications', 'count_value'));
    }
    public function welcome()
    {
        return $this->render('layouts.home');
    }
    public function dashboard()
    {
        //Flash::success('You are welcome back');
        //$user = new User();
        $user = $this->getUser();

        if ($user->hasRole('super')) {
            $totalSchools = School::count();
            $totalPotentialSchools = PotentialSchool::count();
            $totalParents = Guardian::count();
            $totalstudents = Student::count();
            $totalStaff = Staff::count();
            return $this->render('core::dashboard.super', compact('totalPotentialSchools', 'totalSchools', 'totalParents', 'totalstudents', 'totalStaff'));
        }
        if ($user->role == "staff") {
            if ($user->hasRole('proprietor')) {
                return redirect()->route('role_dashboard', 'proprietor');
            }
            if ($user->hasRole('admin')) {
                return redirect()->route('role_dashboard', 'admin');
            }
            if ($user->hasRole('teacher')) {
                return redirect()->route('role_dashboard', 'teacher');
            }
            if ($user->hasRole('account')) {
                return redirect()->route('role_dashboard', 'account');
            }
        }

        if ($user->hasRole('student')) {


            //$student = $this->getStudent();

            return redirect()->route('student_dashboard');
            //$data = compact('user','activities', 'student');
            //return $this->render('core::dashboard.student', $data);
        }

        if ($user->hasRole('parent')) {

            $guardian = $this->getGuardian();
            $wards = substr($guardian->ward_ids, 1);
            $ward_array = explode('~', $wards);

            $no_of_wards =  count($ward_array) - 1;
            $data = compact('guardian', 'user', 'no_of_wards');

            return $this->render('core::dashboard.parent', $data);
        }
    }
    public function superAdminDashboard()
    {
        $totalSchools = School::count();
        $totalPotentialSchools = PotentialSchool::count();
        $totalGuardian = Guardian::count();
        $total_students = Student::ActiveAndSuspended()->count();
        $active_students = Student::ActiveStudentOnly()->count();
        $suspended_students = Student::SuspendedStudentOnly()->count();
        $withdrawn_students = Student::WithdrawnStudentOnly()->count();
        $alumni = Alumni::count();
        $totalStaff = Staff::count();
        return $this->render(compact('totalPotentialSchools', 'totalSchools', 'totalGuardian', 'total_students', 'active_students', 'suspended_students', 'withdrawn_students', 'alumni', 'totalStaff'));
    }
    public function adminDashboard()
    {
        $user = $this->getUser();
        $today = Carbon::now();
        $school_id = $this->getSchool()->id;
        $sess_id = $this->getSession()->id;
        $term_id = $this->getTerm()->id;
        $total_students = Student::ActiveAndSuspended()->where(['school_id' => $school_id])->count();
        $active_students = Student::ActiveStudentOnly()->where(['school_id' => $school_id])->count();
        $suspended_students = Student::SuspendedStudentOnly()->where(['school_id' => $school_id])->count();
        $withdrawn_students = Student::WithdrawnStudentOnly()->where(['school_id' => $school_id])->count();
        $alumni = Alumni::where(['school_id' => $school_id])->count();

        $active_male = Student::ActiveStudentOnly()->join('users', 'users.id', 'students.user_id')
            ->where(['students.school_id' => $school_id, 'users.gender' => 'male'])->count();

        $active_female = Student::ActiveStudentOnly()->join('users', 'users.id', 'students.user_id')
            ->where(['students.school_id' => $school_id, 'users.gender' => 'female'])->count();

        $totalStaff = Staff::where(['school_id' => $school_id])->count();
        $totalGuardian = Guardian::where('school_id', $school_id)->count();

        $activities = AuditTrail::where('school_id', $school_id)->where('created_at', '>', $today->startOfWeek())->orderBy('id', 'DESC')->get();

        return $this->render(compact('total_students', 'active_students', 'active_male', 'active_female', 'suspended_students', 'withdrawn_students', 'alumni', 'totalStaff', 'totalGuardian', 'activities', 'user'));
    }

    public function studentDashboard()
    {
        $school = $this->getSchool();
        $student = $this->getStudent();

        $student_class = StudentsInClass::where(['school_id' => $school->id, 'student_id' => $student->id])->orderby('id', 'DESC')->first();

        $total_in_class = StudentsInClass::where(['school_id' => $school->id, 'sess_id' => $student_class->sess_id, 'class_teacher_id' => $student_class->class_teacher_id])->count();

        $subject_teachers = $student_class->classTeacher->subjectTeachers()->with('subject')->get();
        $current_class = $student_class->classTeacher->c_class->name;
        $class_teacher = $student_class->classTeacher->staff->user;
        $resultObj = new Result();
        $cum_performances = $resultObj->studentCummulativePerformance($student->id);
        $performance_data = [];
        $count = 0;
        $total_performance = 0;
        $average_performance = 0;
        foreach ($cum_performances as $performance) {
            $average = sprintf("%01.2f", $performance['average']);
            $performance_data[] = $average;
            $total_performance += $average;
            if ($average > 0) {
                $count++;
            }
        }
        if ($count > 0) {

            $average_performance = sprintf("%01.2f", $total_performance / $count);
        }
        // $student_in_class =  StudentsInClass::where('student_id', $id)->orderBy('id', 'DESC')->first();
        // $student = $stud->getStudentDetails($id, $student_in_class->class_teacher_id);
        // $class_mates = StudentsInClass::where(['class_teacher_id'=>$student_in_class->class_teacher_id, 'school_id'=>$this->getSchool()->id, 'sess_id' => $this->getSession()->id])->get();
        // $teachers = Staff::where('school_id', $this->getSchool()->id)->get();

        // $performance = 0;
        return  response()->json(compact('student', 'subject_teachers', 'performance_data', 'average_performance', 'total_in_class', 'current_class', 'class_teacher'), 200);
    }
    public function teacherDashboard()
    {
        $routine_obj = new Routine();
        $teacher = new Teacher();
        $school = $this->getSchool();
        $staff = $this->getStaff();
        $class_activities = [];
        $class_teachers =  ClassTeacher::where(['school_id' => $school->id, 'teacher_id' => $staff->id])->get();
        foreach ($class_teachers as $class_teacher) {
            $class_activities = $class_teacher->classActivity()->orderBy('id', "DESC")->get();
        }


        // alex
        // $subjects = $this->getStudents($staff->id);
        $subject_teachers = $teacher->teacherSubjects($staff->id, $school->id);

        return $this->render(compact('staff', 'class_activities', 'class_teachers', 'subject_teachers'));
    }

    public function roleDashboard($role)
    {
        $user = $this->getUser();
        $today = Carbon::now();
        $school_id = $this->getSchool()->id;
        $sess_id = $this->getSession()->id;
        $term_id = $this->getTerm()->id;

        $student_obj = new Student();
        $student_in_class_obj = new StudentsInClass();
        $class_attendance = new ClassAttendance();
        $routine_obj = new Routine();


        if (!$user->hasRole($role)) {
            return redirect()->route('denied');
        }
        $activities = $user->activityLog()->where('created_at', '>', $today->startOfWeek())->orderBy('id', 'DESC')->get();

        $notifications = News::where('school_id', $school_id)
            ->where('targeted_audience', 'like', $user->role)
            ->orWhere('targeted_audience', 'like', $user->role . '~%')
            ->orWhere('targeted_audience', 'like', '%~' . $user->role . '~%')
            ->orWhere('targeted_audience', 'like', '%~' . $user->role)
            ->orderBy('id', 'DESC')->get();

        if ($notifications != '[]') {
            foreach ($notifications as $notification) {

                $seen_by_array  = explode('~', $notification->seen_by);

                $notification->seen_by_array = $seen_by_array;
            }
        }

        $staff = $this->getStaff();




        switch ($role) {

            case 'proprietor':
                $group_of_school = GroupOfSchool::where('proprietor_user_id', $user->id)->first();
                if ($group_of_school) {

                    $schools = $group_of_school->schools;
                    $total_students = 0;
                    $active_students = 0;
                    $active_male = 0;
                    $active_female = 0;
                    $suspended_students = 0;
                    $withdrawn_students = 0;
                    $alumni = 0;
                    $totalStaff = 0;
                    $totalGuardian = 0;
                    foreach ($schools as $school) {
                        $school_id = $school->id;
                        $total_students += Student::ActiveAndSuspended()->where(['school_id' => $school_id])->count();
                        $active_students += Student::ActiveStudentOnly()->where(['school_id' => $school_id])->count();
                        $suspended_students += Student::SuspendedStudentOnly()->where(['school_id' => $school_id])->count();
                        $withdrawn_students += Student::WithdrawnStudentOnly()->where(['school_id' => $school_id])->count();
                        $alumni += Alumni::where(['school_id' => $school_id])->count();

                        $active_male += Student::ActiveStudentOnly()->join('users', 'users.id', 'students.user_id')
                            ->where(['students.school_id' => $school_id, 'users.gender' => 'male'])->count();

                        $active_female += Student::ActiveStudentOnly()->join('users', 'users.id', 'students.user_id')
                            ->where(['students.school_id' => $school_id, 'users.gender' => 'female'])->count();

                        $totalStaff += Staff::where(['school_id' => $school_id])->count();
                        $totalGuardian += Guardian::where('school_id', $school_id)->count();
                    }
                    // $total_students = 0;
                    // $active_students = 0;
                    // $suspended_students = 0;
                    // $withdrawn_students = 0;
                    // $alumni = 0;
                    return $this->render('core::dashboard.proprietor', compact('schools', 'total_students', 'active_students', 'active_male', 'active_female', 'suspended_students', 'withdrawn_students', 'alumni', 'totalStaff', 'totalGuardian', 'staff', 'activities', 'user', 'notifications'));
                } else {
                    $total_students = Student::ActiveAndSuspended()->where(['school_id' => $school_id])->count();
                    $active_students = Student::ActiveStudentOnly()->where(['school_id' => $school_id])->count();
                    $suspended_students = Student::SuspendedStudentOnly()->where(['school_id' => $school_id])->count();
                    $withdrawn_students = Student::WithdrawnStudentOnly()->where(['school_id' => $school_id])->count();
                    $alumni = Alumni::where(['school_id' => $school_id])->count();

                    $active_male = Student::ActiveStudentOnly()->join('users', 'users.id', 'students.user_id')
                        ->where(['students.school_id' => $school_id, 'users.gender' => 'male'])->count();

                    $active_female = Student::ActiveStudentOnly()->join('users', 'users.id', 'students.user_id')
                        ->where(['students.school_id' => $school_id, 'users.gender' => 'female'])->count();

                    $totalStaff = Staff::where(['school_id' => $school_id])->count();
                    $totalGuardian = Guardian::where('school_id', $school_id)->count();

                    return $this->render('core::dashboard.proprietor', compact('total_students', 'active_students', 'active_male', 'active_female', 'suspended_students', 'withdrawn_students', 'alumni', 'totalStaff', 'totalGuardian', 'staff', 'activities', 'user', 'notifications'));
                }
                //fetch specific things you want admin to see on their front page

                break;
            case 'admin':
                //fetch specific things you want admin to see on their front page
                $total_students = Student::ActiveAndSuspended()->where(['school_id' => $school_id])->count();
                $active_students = Student::ActiveStudentOnly()->where(['school_id' => $school_id])->count();
                $suspended_students = Student::SuspendedStudentOnly()->where(['school_id' => $school_id])->count();
                $withdrawn_students = Student::WithdrawnStudentOnly()->where(['school_id' => $school_id])->count();
                $alumni = Alumni::where(['school_id' => $school_id])->count();

                $active_male = Student::ActiveStudentOnly()->join('users', 'users.id', 'students.user_id')
                    ->where(['students.school_id' => $school_id, 'users.gender' => 'male'])->count();

                $active_female = Student::ActiveStudentOnly()->join('users', 'users.id', 'students.user_id')
                    ->where(['students.school_id' => $school_id, 'users.gender' => 'female'])->count();

                $totalStaff = Staff::where(['school_id' => $school_id])->count();
                $totalGuardian = Guardian::where('school_id', $school_id)->count();

                $activities = AuditTrail::where('school_id', $school_id)->where('created_at', '>', $today->startOfWeek())->orderBy('id', 'DESC')->get();

                return $this->render('core::dashboard.admin', compact('total_students', 'active_students', 'active_male', 'active_female', 'suspended_students', 'withdrawn_students', 'alumni', 'totalStaff', 'totalGuardian', 'staff', 'activities', 'user', 'notifications'));
                // return $this->render('core::dashboard.admin', compact('maleStudents', 'femaleStudents', 'totalStudents', 'totalStaff', 'totalGuardian', 'staff', 'activities', 'user', 'notifications'));
                break;

            case 'teacher':
                //fetch specific things you want teachers to see on their front page

                $class_activities = [];
                $class_teachers =  ClassTeacher::where(['school_id' => $school_id, 'teacher_id' => $staff->id])->get();
                foreach ($class_teachers as $class_teacher) {
                    $class_activities = $class_teacher->classActivity()->orderBy('id', "DESC")->get();
                }


                // alex
                $subjects = $this->getStudents($staff->id);
                $routines =  $routine_obj->timeTable($staff->id);
                $details = $this->getClasses();

                return $this->render('core::dashboard.teacher2', compact('routines', 'staff', 'class_activities', 'class_teachers', 'user', 'notifications', 'subjects', 'routines', 'details'));
                //                return $this->render('core::dashboard.teacher', compact('routines', 'staff', 'class_activities', 'user', 'notifications'));
                break;

            case 'account':
                //fetch specific things you want accountants to see on their front page
                return $this->render('core::dashboard.account', compact('staff', 'activities', 'user', 'notifications'));
                break;

            default:
                # code...
                break;
        }


        // if ($user->role == "staff") {


        //     $data = compact('totalStudents', 'totalStaff', 'totalGuardian', 'routines', 'staff', 'activities', 'user', 'notifications');
        //     //return $this->render('core::dashboard.staff', compact('totalStudents','totalStaff','totalGuardian', 'routines', 'staff', 'activities', 'user', 'notifications'));
        // }
    }

    private function getClasses()
    {
        $teacher = new Teacher();

        $id = $this->getStaff()->id;
        $school_id = $this->getSchool()->id;

        $classes = $teacher->teacherClasses($id, $school_id);

        return $classes;
    }

    private function getRoutines($id)
    {
        $routine_obj = new Routine();

        $routines =  $routine_obj->timeTable($id);
        //$routines = $this->fetchRoutine($class_teacher_id, $options);
        //        return $this->render('routines.time_table', compact('routines'));
        return $routines;
    }

    private function getAnalysis($subject_id)
    {
        $terms = Term::select('name', 'id')->get();
        $this->good = 0;
        $results = array();
        foreach ($terms as $term) {
            $result = array();
            $result['name'] = $term->name;
            $result['average'] = Result::where('term_id', '=', $term->id)->where('subject_teacher_id', '=', $subject_id)->avg('total');
            $result['average'] = $result['average'] == null ? 0 : $result['average'];
            Result::where('term_id', '=', $term->id)->where('subject_teacher_id', '=', $subject_id)->each(function ($item, $key) use ($result) {
                if ($item['total'] > $result['average']) {
                    $this->good += 1;
                }
                return true;
            });
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

    function getLeastGrade()
    {
        //        ->take( 1 )->get()->pluck("grade_range");
        //        $range = Grade::all()->sortBy( 'grade_point' )
        $range = Grade::all()->sortBy('grade_point')->take(1)->pluck('grade_range');
        $ranges = explode('-', $range);
        return $ranges[1];
        //        return 0;
    }

    function average($totals)
    {
        if ($totals->count() == 0)
            return 0;
        $sum = 0;
        foreach ($totals as $total)
            $sum += $total;
        return $sum / $totals->count();
    }

    private function getStudents($id)
    {
        $subjects = array();
        $teacher = new Teacher();
        $school_id = $this->getSchool()->id;
        $details = $teacher->teacherSubjects($id, $school_id);
        
        foreach ($details as $detail) {
            if ($detail->subject) {
                //            $class_teacher_id = SubjectTeacher::where( 'teacher_id', '=', $id )->where( 'subject_id', '=', $subject->id )->pluck( 'class_teacher_id' )->first();
                $student_ids = StudentsInClass::where('class_teacher_id', '=', $detail->class_teacher_id)->pluck('student_ids')->first();
                $ids = explode("~", $student_ids);
                $subject = array();
                $subject['name'] = $detail->subject->name;
                $subject['class'] = $detail->classTeacher->c_class->name;
                $subject['id'] = $detail->id;
                $subject['result'] = $this->getAnalysis($detail->id);
                $subject['good'] = $this->good;
                $subject['student_count'] = count($ids) - 1;
                array_push($subjects, $subject);
            }
        }
        //        dd( $subjects );
        return $subjects;
    }


    public function uploadCkeditorFiles(Request $request)
    {
        //return request()->all();

        return $this->uploadImageContent(request()->all());
    }
}