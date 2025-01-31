<?php

namespace App\Http\Controllers\Result;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Result;
use App\Models\CClass;
use App\Models\ClassAttendance;
use App\Models\School;
use App\Models\Student;
use App\Models\Staff;
use App\Models\Teacher;
use App\Models\SubjectTeacher;
use App\Models\ClassTeacher;
use App\Models\StudentsInClass;
use App\Models\Term;
use App\Models\SSession;
use App\Models\ResultAction;
use App\Models\ResultComment;
use App\Models\Remark;

class ResultsController extends Controller
{

    public function necessaryParams()
    {
        $class_object = new CClass();

        $school_id = $this->getSchool()->id;
        $sess_id = $this->getSession()->id;
        $term_id = $this->getTerm()->id;

        /*$curriculum_array = explode('~', $this->school->curriculum);
        $classes = $class_object->getCurriculumClasses ($this->school, $curriculum_array);

        $class_array = [];
        foreach ( $classes as $class) :
            $class_array[$class->id] = alternateClassName($class->name);
        endforeach;*/

        $classTeacher = new ClassTeacher();
        $class_teachers = ClassTeacher::where('school_id', $this->getSchool()->id)->get();
        $classes = $classTeacher->getClassTeachers($class_teachers, $sess_id, $term_id, $school_id);


        $levels = $this->getLevels(); //Level::all();
        $level_array = ['' => 'Select Level'];

        foreach ($levels as $level) {
            $level_array[$level->id] = formatLevel($level->description);
        }

        $terms = Term::get();
        foreach ($terms as $term) :
            $term_array[$term->id] = $term->name;
        endforeach;

        $sessions = SSession::orderBy('id', 'DESC')->get();
        foreach ($sessions as $session) :
            $session_array[$session->id] = $session->name;
        endforeach;

        return array($classes, $level_array, $term_array, $session_array);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // $request = request()->all();

        // $subject_teacher_id = 0; //set to zero if no specific subject is selected
        // if (isset($request['sti']) && $request['sti'] != "") {
        //     $subject_teacher_id = $request['sti'];
        // }

        // $teacher = new Teacher();

        // $id = $this->getStaff()->id;
        // $all_sessions = SSession::all();
        // $sessions = [];
        // foreach ($all_sessions as $all_session) :
        //     $sessions[$all_session->id] = $all_session->name;
        // endforeach;
        // $details = $teacher->teacherSubjects($id);

        // $subject_details = [];
        // foreach ($details as $detail) :
        //     $subject_details[$detail->id] = $detail->subject->name . ' for ' . $detail->class->name; //.' ('.$detail->level->level.')';
        // endforeach;

        // if (isset($request['load_selection_form'])) {
        //     return $this->render('results.record_result_selection_form', compact('details', 'subject_details', 'sessions', 'subject_teacher_id'));
        // }
        // return $this->render('results.index', compact('details', 'subject_details', 'sessions', 'subject_teacher_id'));
    }


    public function home()
    {
        list($classes, $level_array, $term_array, $session_array) = $this->necessaryParams();

        $class_teachers = ClassTeacher::where('school_id', $this->getSchool()->id)->orderBy('class_id')->get();

        $class_array = ['' => 'Select Class'];
        foreach ($class_teachers as $class_teacher) {
            $class_array[$class_teacher->id] = $class_teacher->c_class->name;
        }



        return $this->render('results.home', compact('classes', 'level_array', 'term_array', 'session_array', 'class_array'));
    }

    public function setSelectionOptions(Request $request)
    {
        $school = $this->getSchool();
        $user = $this->getUser();

        $teacher = $this->getStaff();
        $levels = [];
        $class_teachers = [];
        if ($user->hasPermission('can manage results')) {

            $levels = $this->getLevels();
        } else {
            $class_teachers = ClassTeacher::with('c_class', 'level.levelGroup')->where(['school_id' => $school->id, 'teacher_id' => $teacher->id])->get();
        }
        $subject_teachers = [];
        if ($teacher) {
            $subject_teachers = $teacher->subjectTeachers()->with(['subject', 'classTeacher.c_class'])
                ->where(['school_id' => $school->id])->get();
        }

        $terms = Term::get();
        $sessions = SSession::where('id', '<=', $school->current_session)->orderBy('id', 'DESC')->get();

        return response()->json(compact('subject_teachers', 'terms', 'sessions', 'levels', 'class_teachers'), 200);
    }

    public function studentSelectionOptions(Request $request)
    {
        if (isset($request->student_id) && $request->student_id !== '' && $request->student_id !== 'NaN') {

            $student_id = $request->student_id;
        } else {

            $student_id = $this->getStudent()->id;
        }
        $school = $this->getSchool();
        $terms = Term::get();
        $sessions = SSession::where('id', '<=', $school->current_session)->orderBy('id', 'DESC')->get();
        $my_classes = StudentsInClass::with('classTeacher.c_class')->where([
            'student_id' => $student_id,
            'school_id' => $school->id,
        ])->get();
        return  response()->json(compact('my_classes', 'terms', 'sessions', 'student_id'), 200);
    }

    // public function getSubjectStudent(Request $request)
    // {
    //     $result = new Result();
    //     $result_action_obj = new ResultAction();
    //     $teacher = new Teacher();

    //     $subject_teacher_id = $request->subject_teacher_id;
    //     $sub_term = $request->sub_term;

    //     $subject_teacher = SubjectTeacher::with('subject')->find($subject_teacher_id);

    //     $class_teacher = $subject_teacher->classTeacher;
    //     $class_teacher_id = $class_teacher->id;
    //     $class = CClass::find($class_teacher->class_id);
    //     $curriculum_level_group_id = $class_teacher->level->curriculum_level_group_id;

    //     $grades = $this->getLevelGrades($curriculum_level_group_id);

    //     $teacher_id = $this->getStaff()->id;

    //     if ($subject_teacher->teacher_id != $teacher_id) {
    //         return response()->json(['error'], 403);
    //     }

    //     $school_id = $this->getSchool()->id;
    //     $term_id = $this->getTerm()->id; //current term

    //     $sess_id = $this->getSession()->id; //current session

    //     $result_settings = ResultDisplaySetting::where(['school_id' => $school_id, 'curriculum_level_group_id' => $curriculum_level_group_id])->first();
    //     //if the teacher makes specific session and term section, we use this instead
    //     if (isset($request->sess_id, $request->term_id)) {
    //         $term_id = $request->term_id; //selected term

    //         $sess_id = $request->sess_id; //selected;
    //     }

    //     $term_action = 'actions_term_' . $term_id;
    //     $action_result = $result_action_obj->performResultAction($school_id, $subject_teacher_id, $sess_id);

    //     $result_actions = $action_result->$term_action;
    //     list($edit_midterm, $edit_ca1, $edit_ca2, $edit_ca3, $edit_ca4, $edit_ca5, $edit_exam) = $result->getAllResultActions($result_actions);

    //     $result_action_array = $result->resultStatusAction($result_actions);

    //     $active_assessment = $sub_term;

    //     $class_students = $teacher->teacherSubjectStudents($subject_teacher, $sess_id, $term_id, $school_id);

    //     $students = [];
    //     $empty_half_record = 0;
    //     $empty_full_record = 0;
    //     if (!empty($class_students)) {
    //         foreach ($class_students as $student) :
    //             $student_id = $student->id;

    //             $reg_no = $student->registration_no;
    //             //check whether any record exits for this subject and student if not create one
    //             $result_detail = $result->studentResult($sess_id, $student_id, $reg_no, $school_id, $term_id, $subject_teacher_id, $class_teacher_id, $teacher_id);

    //             $mid_term = $result_detail->mid_term / 10;
    //             $ca1 = $result_detail->ca1;
    //             $ca2 = $result_detail->ca2;
    //             $ca3 = $result_detail->ca3;



    //             $result_detail->test = $result->addScores($mid_term, $ca1, $ca2, $ca3);

    //             $student->result_detail = $result_detail;

    //             list($student_half_record,  $student_full_record) = $this->analyzeProgress($result_detail, $result_settings);

    //             $empty_half_record += $student_half_record;
    //             $empty_full_record += $student_full_record;
    //             $students[] = $student;
    //         endforeach;
    //     }
    //     $subject_teacher->empty_half_record = $empty_half_record;
    //     $subject_teacher->empty_full_record = $empty_full_record;
    //     $data = compact('students', 'subject_teacher_id', 'active_assessment', 'edit_midterm', 'edit_ca1', 'edit_ca2', 'edit_ca3', 'edit_exam', 'result_action_array', 'subject_teacher', 'class', 'term_id', 'sess_id');

    //     $csv = false;
    //     if (isset($request->csv) && ($request->csv == 'true')) {
    //         $csv = true;
    //         $data['csv'] = $csv;
    //     }
    //     return response()->json($data, 200);
    // }
    public function getSubjectStudent(Request $request)
    {
        $result = new Result();
        $result_action_obj = new ResultAction();
        $teacher = new Teacher();


        $school_id = $this->getSchool()->id;
        $term_id = $this->getTerm()->id; //current term

        $sess_id = $this->getSession()->id; //current session

        $subject_teacher_id = $request->subject_teacher_id;
        $sub_term = $request->sub_term;

        $subject_teacher = SubjectTeacher::with('subject', 'classTeacher.c_class', 'classTeacher.level')->find($subject_teacher_id);

        $class_teacher = $subject_teacher->classTeacher;
        $class_teacher_id = $class_teacher->id;
        $class = $class_teacher->c_class;
        $curriculum_level_group_id = $class_teacher->level->curriculum_level_group_id;

        $result_settings = $this->getResultSettings($curriculum_level_group_id);
        $grades = $this->getLevelGrades($curriculum_level_group_id);

        $teacher_id = $this->getStaff()->id;

        if ($subject_teacher->teacher_id != $teacher_id) {
            return response()->json(['error'], 403);
        }


        //if the teacher makes specific session and term section, we use this instead
        if (isset($request->sess_id, $request->term_id)) {
            $term_id = $request->term_id; //selected term

            $sess_id = $request->sess_id; //selected;
        }

        $term_action = 'actions_term_' . $term_id;
        $action_result = $result_action_obj->performResultAction($school_id, $subject_teacher_id, $sess_id);

        // $result_actions = $action_result->$term_action;

        $result_action_array = [];

        $active_assessment = $sub_term;

        $class_students = $teacher->teacherSubjectStudents($subject_teacher, $sess_id, $term_id, $school_id);

        $students = [];
        $empty_half_record = 0;
        $empty_full_record = 0;
        $edit_midterm = $edit_exam = true;
        if (!empty($class_students)) {
            foreach ($class_students as $student) :
                if ($student->studentship_status !== 'left') {


                    $student_id = $student->id;

                    $reg_no = $student->registration_no;
                    //check whether any record exits for this subject and student if not create one
                    $result_detail = $result->studentResult($sess_id, $student_id, $reg_no, $school_id, $term_id, $subject_teacher_id, $class_teacher_id, $teacher_id);

                    $result_action_array = $result->resultStatusAction($result_detail);

                    list($edit_midterm, $edit_exam) = $result->getAllResultActions($result_detail);

                    // $mid_term = $result_detail->mid_term / 10;
                    // $ca1 = $result_detail->ca1;
                    // $ca2 = $result_detail->ca2;
                    // $ca3 = $result_detail->ca3;
                    // $ca4 = $result_detail->ca4;
                    // $ca5 = $result_detail->ca5;
                    $result_detail = $result->updateResultDetails($result_detail, $result_settings);
                    list($student_half_record,  $student_full_record) = $this->analyzeProgress($result_detail, $result_settings);


                    $student->result_detail = $result_detail;

                    $empty_half_record += $student_half_record;
                    $empty_full_record += $student_full_record;
                    $students[] = $student;
                }
            endforeach;
        }
        $subject_teacher->empty_half_record = $empty_half_record;
        $subject_teacher->empty_full_record = $empty_full_record;
        $term = Term::find($term_id);
        $session = SSession::find($sess_id);
        $data = compact('students', 'subject_teacher_id', 'active_assessment', 'edit_midterm', 'edit_exam', 'result_action_array', 'subject_teacher', 'class', 'term_id', 'sess_id', 'term', 'session', 'result_settings');

        $csv = false;
        if (isset($request->csv) && ($request->csv == 'true')) {
            $csv = true;
            $data['csv'] = $csv;
        }
        return $this->render($data);
    }
    private function analyzeProgress($result_detail, $result_settings)
    {
        $empty_half_record = 0;
        $empty_full_record = 0;
        $no_of_ca = $result_settings->no_of_ca;
        $no_of_ca_for_midterm = $result_settings->no_of_ca_for_midterm;
        for ($i = 1; $i <= $no_of_ca; $i++) {
            $assessment = 'ca' . $i;
            if ($i <= $no_of_ca_for_midterm) {
                // check midterm progress
                if ($result_detail->$assessment == null) {
                    $empty_half_record++;
                    $empty_full_record++;
                }
            } else {
                if ($result_detail->$assessment == null) {
                    $empty_full_record++;
                }
            }
        }

        if ($result_detail->effort == null) {
            $empty_half_record++;
            $empty_full_record++;
        }
        if ($result_detail->behavior == null) {
            $empty_half_record++;
            $empty_full_record++;
        }
        if ($result_detail->exam == null) {
            $empty_full_record++;
        }


        return array($empty_half_record,  $empty_full_record);
    }
    // private function saveMidTermScore($mid_term_score, $result_detail, $result_settings)
    // {
    //     $no_of_ca_for_midterm = $result_settings->no_of_ca_for_midterm;
    //     $total_midterm_score = 0;
    //     $total_denominator = 0;
    //     for ($i = 1; $i <= $no_of_ca_for_midterm; $i++) {
    //         $assessment = 'ca' . $i;
    //         $attainable_score = $result_settings->$assessment;
    //         $total_denominator += $result_settings->$assessment;

    //         $ca_score_converted_from_mid_term = $attainable_score / 100 * $mid_term_score;
    //         $result_detail->$assessment = $ca_score_converted_from_mid_term;
    //         $result_detail->save();
    //         // $total_midterm_score += ($result_detail->$assessment) ? $result_detail->$assessment : 0;
    //     }
    //     // $midterm_score_in_100_percent = $total_midterm_score / $total_denominator * 100;
    //     // $result_detail->mid_term = $midterm_score_in_100_percent;
    //     // $result_detail->save();
    // }

    private function saveMidTermScore($mid_term_score, $result_detail, $result_settings)
    {
        $no_of_ca_for_midterm = $result_settings->no_of_ca_for_midterm;
        $total_midterm_score = 0;
        $total_denominator = 0;
        if ($no_of_ca_for_midterm > 1) {
            for ($i = 1; $i <= $no_of_ca_for_midterm; $i++) {
                $assessment = 'ca' . $i;
                $total_denominator += $result_settings->$assessment;
                $total_midterm_score += ($result_detail->$assessment) ? $result_detail->$assessment : 0;
            }
            $midterm_score_in_100_percent = $total_midterm_score / $total_denominator * 100;
            $result_detail->mid_term = $midterm_score_in_100_percent;
            $result_detail->save();
        } else {
            $assessment = 'ca1';
            $attainable_score = $result_settings->ca1;
            $ca_score_converted_from_mid_term = $attainable_score / 100 * $mid_term_score;
            $result_detail->ca1 = $ca_score_converted_from_mid_term;
            $result_detail->save();
        }
    }

    public function normalizeResult(Request $request)
    {
        $result = new Result();
        $school_id = $this->getSchool()->id;
        $sess_id = $this->getSession()->id;
        $term_id = $this->getTerm()->id;

        //if the teacher makes specific session and term section, we use this instead
        if (isset($request->sess_id, $request->term_id)) {
            $term_id = $request->term_id; //selected term

            $sess_id = $request->sess_id; //selected;
        }
        $subject_teacher_id = $request->subject_teacher_id;
        $subject_teacher = SubjectTeacher::find($subject_teacher_id);

        $class_teacher = $subject_teacher->classTeacher;
        $curriculum_level_group_id = $class_teacher->level->curriculum_level_group_id;

        $result_settings = $this->getResultSettings($curriculum_level_group_id);
        $class_teacher_id = $class_teacher->id;
        $teacher_id = $this->getStaff()->id;
        $teacher = new Teacher();
        $class_students = $teacher->teacherSubjectStudents($subject_teacher, $sess_id, $term_id, $school_id);
        foreach ($class_students as $student) :
            if ($student->studentship_status !== 'left') {
                $student_id = $student->id;

                $reg_no = $student->registration_no;
                //check whether any record exits for this subject and student if not create one
                $student_result_detail = $result->studentResult($sess_id, $student_id, $reg_no, $school_id, $term_id, $subject_teacher_id, $class_teacher_id, $teacher_id);

                // $this->saveMidTermScore($student_result_detail->mid_term, $student_result_detail, $result_settings);

                $exam = $student_result_detail->exam;

                $test = $result->addCaScores($student_result_detail, $result_settings);

                //add the total and save
                $total = $result->addScores($test, $exam);
                $student_result_detail->comments = defaultComment($total);
                $student_result_detail->total = $total;
                $student_result_detail->save();
            }
        endforeach;
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function recordResult(Result $result, Request $request)
    {
        //
        // return $request;
        $subject_teacher_id = $request->subject_teacher_id;
        $label = $request->assessment; //e.g ca1,ca2,ca3,exam,comments, behavior, effort

        $score = $request->score;
        $student_id = $request->student_id;
        $school_id = $this->getSchool()->id;
        $sess_id = $this->getSession()->id;
        $term_id = $this->getTerm()->id;
        $sub_term = $request->sub_term;

        //if the teacher makes specific session and term section, we use this instead
        if (isset($request->sess_id, $request->term_id)) {
            $term_id = $request->term_id; //selected term

            $sess_id = $request->sess_id; //selected;
        }
        $subject_teacher = SubjectTeacher::with('subject')->find($subject_teacher_id);

        $class_teacher = $subject_teacher->classTeacher;
        $curriculum_level_group_id = $class_teacher->level->curriculum_level_group_id;

        $result_settings = $this->getResultSettings($curriculum_level_group_id);

        $class_teacher_id = $class_teacher->id;
        $teacher_id = $this->getStaff()->id;

        if ($subject_teacher->teacher_id != $teacher_id) {
            return response()->json(compact('Forbidden'), 403);
        }

        $reg_no = Student::find($student_id)->registration_no;
        //check whether any record exits for this subject and student if not create one
        $student_result_detail = $result->studentResult($sess_id, $student_id, $reg_no, $school_id, $term_id, $subject_teacher_id, $class_teacher_id, $teacher_id);

        //we need this to estimate result entry progress by of subject teacher
        $new_entry = false;
        if ($score != '') {
            if ($student_result_detail->$label == null) {
                $new_entry = true;
            }
        }


        $student_result_detail->$label = $score;

        // if ($sub_term === 'half') {

        //     $this->saveMidTermScore($score, $student_result_detail, $result_settings);
        // }
        //$result_detail->save();

        // $mid_term = $student_result_detail->mid_term / 10; // we convert mid_term from 100 to over 10
        // $ca1 = $student_result_detail->ca1;
        // $ca2 = $student_result_detail->ca2;
        // $ca3 = $student_result_detail->ca3;
        // $ca4 = $student_result_detail->ca4;
        // $ca5 = $student_result_detail->ca5;
        $exam = $student_result_detail->exam;

        $test = $result->addCaScores($student_result_detail, $result_settings);

        //add the total and save
        $total = $result->addScores($test, $exam);

        if ($label != 'mid_term') {
            $student_result_detail->comments = defaultComment($total);
        }
        $student_result_detail->total = $total;
        $student_result_detail->save();
        // return $student_result_detail;
        $teacher = new Teacher();
        $class_students = $teacher->teacherSubjectStudents($subject_teacher, $sess_id, $term_id, $school_id);

        $students = [];
        $empty_half_record = 0;
        $empty_full_record = 0;
        if (!empty($class_students)) {
            foreach ($class_students as $student) :
                if ($student->studentship_status !== 'left') {
                    $student_id = $student->id;

                    $reg_no = $student->registration_no;
                    //check whether any record exits for this subject and student if not create one
                    $result_detail = $result->studentResult($sess_id, $student_id, $reg_no, $school_id, $term_id, $subject_teacher_id, $class_teacher_id, $teacher_id);

                    // $mid_term = $result_detail->mid_term / 10;
                    // $ca1 = $result_detail->ca1;
                    // $ca2 = $result_detail->ca2;
                    // $ca3 = $result_detail->ca3;



                    $result_detail = $result->updateResultDetails($result_detail, $result_settings);

                    $student->result_detail = $result_detail;

                    list($student_half_record,  $student_full_record) = $this->analyzeProgress($result_detail, $result_settings);

                    $empty_half_record += $student_half_record;
                    $empty_full_record += $student_full_record;

                    $students[] = $student;
                }
            endforeach;
        }

        $subject_teacher->empty_half_record = $empty_half_record;
        $subject_teacher->empty_full_record = $empty_full_record;
        return response()->json(compact('new_entry', 'test', 'students', 'subject_teacher', 'student_result_detail'));
    }

    public function uploadBulkResult(Result $result, Request $request)
    {
        if ($request->file('result_csv_file') != null && $request->file('result_csv_file')->isValid()) {

            $school_id = $this->getSchool()->id;
            $sess_id = $this->getSession()->id;
            $term_id = $this->getTerm()->id;
            $subject_teacher_id = $request->subject_teacher_id;
            $subject_teacher = SubjectTeacher::find($subject_teacher_id);
            $class_teacher = $subject_teacher->classTeacher;
            $class_teacher_id = $class_teacher->id;
            $curriculum_level_group_id = $class_teacher->level->curriculum_level_group_id;

            $result_settings = $this->getResultSettings($curriculum_level_group_id);
            $teacher_id = $this->getStaff()->id;

            $result_csv_file = $request->file('result_csv_file');


            if ($result_csv_file->getClientOriginalExtension() == 'csv') {

                $path = $result_csv_file->getRealPath();

                $csvAsArray = array_map('str_getcsv', file($path));

                /* Map Rows and Loop Through Them */
                $header = array_shift($csvAsArray);
                $csv    = [];
                foreach ($csvAsArray as $row) {
                    $csv[] = array_combine($header, $row);
                }

                $students = [];
                foreach ($csv as $csvRow) {



                    try {


                        $reg_no = trim($csvRow['STUDENT_ID']);

                        $student = Student::where(['school_id' => $school_id, 'registration_no' => $reg_no])->first();

                        $student_id = $student->id;

                        //check whether this student has his result recorded
                        $result_detail = $result->studentResult($sess_id, $student_id, $reg_no, $school_id, $term_id, $subject_teacher_id, $class_teacher_id, $teacher_id);

                        $result_detail->mid_term = trim($csvRow['MID_TERM']);
                        $result_detail->ca1 = trim($csvRow['CA_1']);
                        $result_detail->ca2 = trim($csvRow['CA_2']);
                        $result_detail->ca3 = trim($csvRow['CA_3']);
                        $result_detail->exam = trim($csvRow['EXAM']);
                        $result_detail->effort = trim($csvRow['ACADEMIC_EFFORT']);
                        $result_detail->behavior = trim($csvRow['CLASS_BEHAVIOUR']);

                        $exam = $result_detail->exam;

                        $test = $result->addCaScores($result_detail, $result_settings);

                        //add the total and save
                        $result_detail->total = $result->addScores($test, $exam);

                        $result_detail->comments = trim($csvRow['REMARK']);

                        if ($result_detail->comments == "") {
                            $result_detail->comments = defaultComment($result_detail->total);
                        }

                        $result_detail->save();

                        $student->result_detail = $result_detail;
                        $students[] = $student;
                    } catch (\Exception $e) {
                        $message =  '<i class="fa fa-info-circle"></i> Caught exception: ' .  $e->getMessage() . '<br> This is due to invalid/modified file header names. Ensure you did not modify the header names of the downloaded format. You may want to download the format by clicking the <code>Click To Download CSV Format</code> button below';

                        return response()->json(['message' => $message], 200);
                    }
                }

                return response()->json(['message' => 'success', 'students' => $students], 200);
            } else {
                $message = 'Please Upload .csv file';
                return response()->json(['message' => $message], 200);
            }
        } else {

            $message = 'Invalid File';
            return response()->json(['message' => $message], 200);
        }
    }

    public function resultAction(Request $request)
    {
        $subject_teacher_id = $request->id;
        $term_id = $request->term_id; //selected term

        $school_id = $this->getSchool()->id;

        $assessment = $request->assessment; //half or full

        $sess_id = $request->sess_id; //selected session

        $action = $request->action;

        $results = Result::where(['sess_id' => $sess_id, 'term_id' => $term_id, 'subject_teacher_id' => $subject_teacher_id, 'school_id' => $school_id, 'result_status' => 'Applicable'])->get();

        foreach ($results as $result) {
            if ($assessment === 'half') {
                $result->midterm_status = $action;
            } else {
                $result->fullterm_status = $action;
            }
            $result->save();
        }




        $subject_teacher = SubjectTeacher::find($subject_teacher_id);

        $subject = $subject_teacher->subject->name;
        $class = CClass::find($subject_teacher->classTeacher->class_id);

        if ($action != 'save') {
            $request->class_teacher_id = $subject_teacher->class_teacher_id;
            //we dont want to record save events...intead we want submit,approve, publish, disapprove etc
            $event_action = ucwords($action) . " students " . $subject . " " . ucwords($assessment) . "-Term result for " . $class->name;

            $this->teacherStudentEventTrail($request, $event_action, 'class');
            //$this->auditTrailEvent($request, $event_action);

        }

        $result_obj = new Result();
        $edit_midterm = $edit_exam = true;
        $result_action_array = ['false', 'false', 'Not Submitted', 'Not Submitted'];
        if ($results->isNotEmpty()) {

            list($edit_midterm, $edit_exam) = $result_obj->getAllResultActions($results[0]);
            $result_action_array = $result_obj->resultStatusAction($results[0]);
            # code...
        }

        return response()->json(compact('edit_midterm', 'edit_exam', 'result_action_array'));
    }

    public function activateAssessmentsView()
    {
        $active_assessment = $this->school->active_assessment;
        return $this->render('results.activate_assessment', compact('active_assessment'));
    }

    public function activateAssessments(Request $request)
    {
        $school = School::find($this->getSchool()->id);
        $school->active_assessment = $request->assessment;
        if ($school->save()) {
            return '<h4><div class="label label-success">Action Successful</div></h4>';
        }
        return '<h4><div class="label label-danger">Action Failed</div></h4>';
    }

    public function resultViewClass()
    {
        $class_object = new CClass();

        $curriculum_array = explode('~', $this->school->curriculum);
        $classes = $class_object->getCurriculumClasses($this->school, $curriculum_array);

        $class_array = [];
        foreach ($classes as $class) :
            $class_array[$class->id] = alternateClassName($class->name);
        endforeach;

        $terms = Term::get();
        $term_array = ['' => 'Select Term'];
        foreach ($terms as $term) :
            $term_array[$term->id] = $term->name;
        endforeach;

        $sessions = SSession::get();
        $session_array = ['' => 'Select Session'];
        foreach ($sessions as $session) :
            $session_array[$session->id] = $session->name;
        endforeach;

        return  $this->render('results.class_view', compact('classes', 'class_array', 'term_array', 'session_array'));
    }


    //This processes Class Broad Sheet
    public function classBroadSheet(Request $request, Result $result)
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        $user = $this->getUser();
        $class_teacher_id = $request->class_teacher_id;
        //$term_spec = $request->term_spec;
        $class_teacher = ClassTeacher::with('c_class', 'level')->find($class_teacher_id);

        $class_name = $class_teacher->c_class->name;

        $school_id = $this->getSchool()->id;
        $curriculum_level_group_id = $class_teacher->level->curriculum_level_group_id;
        $grades = $this->getLevelGrades($curriculum_level_group_id);
        $result_settings = $this->getResultSettings($curriculum_level_group_id);
        $sess_id = $request->sess_id;
        $term_id = $request->term_id;
        $sub_term = $request->sub_term;


        $all_students_in_class = StudentsInClass::with([
            'student.user',
            'classTeacher.subjectTeachers.subject'
        ])->where([
            'class_teacher_id' => $class_teacher_id,
            'school_id' => $school_id,
            'sess_id' => $sess_id,
            //'term_id'=>$term_id
        ])->get();

        $result_averages = []; //keep the averages for each student in an array to eable ranking
        $result_subjects = [];
        $result_details = [];
        $students_in_class = [];
        if ($all_students_in_class->isNotEmpty()) {
            foreach ($all_students_in_class as $student_in_class) {
                $student = $student_in_class->student;

                if ($student->studentship_status !== 'left') {

                    $options = [
                        'class_teacher_id' => $class_teacher_id,
                        'school_id' => $school_id,
                        'sess_id' => $sess_id,
                        'term' => $term_id,
                        'sub_term' => $sub_term,
                        'grades' => $grades,
                        'result_settings' => $result_settings,
                    ];
                    $student_result = $result->analyseStudentsResult($student, $options);

                    $result_averages[$student_in_class->student_id] = $student_result->average; //keep the averages for each student in an array to eable ranking
                    //if(!empty($student_result->subjects)){
                    // $result_subjects = SubjectTeacher::with('subject')->where([
                    //     'class_teacher_id' => $class_teacher_id,
                    //     'school_id' => $school_id
                    // ])->orderBy('id')->get();
                    $result_subjects = array_unique(array_merge($result_subjects, $student_result->subjects));
                    //}


                    $student_in_class->student_result = $student_result;

                    $result_details[$student_in_class->student_id] = $student_result->result_details;

                    $student_remark = Remark::where([
                        'class_teacher_id' => $class_teacher_id,
                        'school_id' => $school_id,
                        'sess_id' => $sess_id,
                        'term_id' => $term_id,
                        'sub_term' => $sub_term,
                        'student_id' => $student->id
                    ])->first();
                    if (!$student_remark) {

                        $student_remark = new Remark();
                        $student_remark->school_id = $school_id;
                        $student_remark->class_teacher_id = $class_teacher_id;
                        $student_remark->sess_id = $sess_id;
                        $student_remark->term_id = $term_id;
                        $student_remark->sub_term = $sub_term;
                        $student_remark->student_id = $student->id;
                        $student_remark->teacher_id = $student_in_class->classTeacher->teacher_id;
                        $student_remark->class_teacher_status == 'default';
                        $student_remark->head_teacher_status == 'default';
                    }

                    $student_name = ucwords(strtolower($student->user->first_name . ' ' . $student->user->last_name));
                    if ($student_remark->class_teacher_status == 'default') {
                        $student_remark->class_teacher_remark = ResultComment::getComment($student_name,    $student_result->result_details_array, $student_result->average, 'class_teacher');
                    }

                    if ($student_remark->head_teacher_status == 'default') {
                        $student_remark->head_teacher_remark = ResultComment::getComment($student_name, $student_result->result_details_array, $student_result->average, 'head_teacher');
                    }

                    //this does the auto remark for each student

                    $student_remark->save();

                    $can_give_principal_remark = false;
                    $can_give_teacher_remark = false;
                    if ($user->hasRole('admin') || $user->hasRole('principal')) {
                        $can_give_principal_remark = true;
                    }
                    if ($this->getStaff()) {
                        if ($this->getStaff()->id === $class_teacher->teacher_id) {
                            $can_give_teacher_remark = true;
                        }
                    }
                    $student_in_class->student_remark = $student_remark;

                    $students_in_class[] = $student_in_class;
                    //$head_teacher_remarks[$student->id] = $student_remark->head_teacher_remark;
                    //$class_teacher_remarks[$student->id] = $student_remark->class_teacher_remark;
                }
            }


            return $this->render(compact('students_in_class', 'result_details', 'result_subjects', 'result_averages', 'sub_term', 'class_teacher_id', 'term_id', 'sess_id', 'can_give_principal_remark', 'can_give_teacher_remark', 'class_name'));
        }


        /*$message = "No student found in ".$class_name.' for '.SSession::find($sess_id)->name.' session';

        return $this->render('errors.404', compact('message'));*/
    }


    public function getStudentResultDetails(Request $request, Result $result)
    {
        // return $request;
        $school = $this->getSchool();
        $school_id = $school->id;
        $sess_id = (int) $request->sess_id;
        $term_id = (int) $request->term_id;
        $student_id = (int) $request->student_id;
        $class_teacher_id = (int) $request->class_teacher_id;

        $student_in_class = StudentsInClass::with(['classTeacher.staff' => function ($q) {
            $q->withTrashed();
        }, 'classTeacher.staff.user' => function ($q) {
            $q->withTrashed();
        }, 'student.user', 'classTeacher.c_class'])->where([
            'class_teacher_id' => $class_teacher_id,
            'sess_id' => $sess_id,
            // 'term_id' => $term_id,
            'school_id' => $school_id,
            'student_id' => $student_id
        ])->first();



        if (!$student_in_class) {
            $message = "RECORD NOT FOUND";
            //return 'RESULT NOT FOUND';
            //return redirect()->route('view_student_results');
            return response()->json(['error' => $message], 404);
        }

        $this_session = SSession::find($sess_id);
        $this_term = Term::find($term_id);

        $term_spec = $request->sub_term;
        $single = $request->single;
        $noprint = 0;
        if (isset($request->noprint)) {
            //return $request->pv;
            $noprint = $request->noprint;
        }

        $view_others = 0;
        $stud = 1;
        if ($request->stud == 1) {

            //$student_id = $this->student_id;
            if ($this->getUser()->hasRole('student')) {
                # code...
                $student_id = $this->getStudent()->id;
            }
            if ($this->getUser()->hasRole('staff')) {
                $stud = 0;
                $view_others = 1;
            }
        } else {

            $stud = 0;
            $view_others = 1;
        }

        $class_teacher = ClassTeacher::find($class_teacher_id);
        $curriculum_level_group_id = $class_teacher->level->curriculum_level_group_id;
        $grades = $this->getLevelGrades($curriculum_level_group_id);
        $result_settings = $this->getResultSettings($curriculum_level_group_id);
        $options = [
            'class_teacher_id' => $class_teacher_id,
            'school_id' => $school_id,
            'sess_id' => $sess_id,
            'term' => $term_id,
            'sub_term' => $term_spec,
            'grades' => $grades,
            'result_settings' => $result_settings,
        ];
        //fetch class teacher and principal remark
        $student_remark = Remark::where([
            'class_teacher_id' => $class_teacher_id,
            'school_id' => $school_id,
            'sess_id' => $sess_id,
            'term_id' => $term_id,
            'sub_term' => $term_spec,
            'student_id' => $student_id
        ])->first();
        $student_results = Result::with(['subjectTeacher.staff.user', 'subjectTeacher.subject'])->where(
            [
                'class_teacher_id' => $class_teacher_id,
                'school_id' => $school_id,
                'sess_id' => $sess_id,
                'term_id' => $term_id,
                'student_id' => $student_id,
                'result_status' => 'Applicable'
            ]
        )->get();

        $students_in_class = StudentsInClass::where([
            'class_teacher_id' => $class_teacher_id,
            'sess_id' => $sess_id,
            // 'term_id' => $term_id,
            'school_id' => $school_id,
        ])->get();

        $no_in_class = $students_in_class->count();

        $result_averages = []; //keep the averages for each student in an array to eable ranking
        $student_average = 0;
        foreach ($students_in_class as $class_student) {
            $analyzed_result = $result->analyseStudentsResult($class_student->student, $options);
            $result_averages[] = $analyzed_result->average; //keep the averages for each student in an array to eable ranking
            if ($class_student->student_id === $student_id) {
                $student_average = $analyzed_result->average;
            }
        }
        if (!$student_results->isEmpty()) {

            list($student_results, $total_subject_class_average, $total_student_score, $result_count) = $result->processStudentResults($student_results, $options);

            if ($result_count == 0) {
                $class_average = 0;
                $position = "";
                $class_average_color = '';
            } else {
                $class_average = sprintf("%01.1f", $total_subject_class_average / $result_count);
                $position = rankResult($student_average, $result_averages);

                list($class_average_result_grade, $class_average_color, $class_average_grade_point) = $result->resultGrade($class_average, $grades);

                $class_average_color = $class_average_color;

                list($student_average_result_grade, $student_average_color, $student_average_grade_point) = $result->resultGrade($student_average, $grades);
                $student_average_color = $student_average_color;
            }
            $class_attendance_obj = new ClassAttendance();
            $class_attendance_score = $class_attendance_obj->studentClassAttendanceScore($student_id, $school_id, $class_teacher_id, $sess_id, $term_id);

            $student_in_class->class_attendance = sprintf("%01.1f", $class_attendance_score);
            //return  $student_results;
            //fetch behavoral and skill ratings for this student
            $student_details = $student_in_class->student;
            $behavior = $student_details->behaviors()
                ->where(['school_id' => $school_id, 'sess_id' => $sess_id, 'term_id' => $term_id])
                ->first();

            $skill = $student_details->skills()
                ->where(['school_id' => $school_id, 'sess_id' => $sess_id, 'term_id' => $term_id])
                ->first();

            $ratings = ratings();
            //return $student_average;
            //return $students;
            //if($request->ajax() ){

            return response()->json(compact('student_results', 'term_spec', 'class_teacher_id', 'sess_id', 'term_id', 'view_others', 'this_session', 'this_term', 'student_in_class', 'behavior', 'skill', 'grades', 'class_average', 'student_average', 'single', 'noprint', 'stud', 'ratings', 'student_remark', 'school', 'no_in_class', 'position', 'result_settings'), 200);
        }
        $message = "RECORD NOT FOUND";

        return response()->json(['error' => $message], 404);


        // }
    }



    /**
     *This function fetches the results for approval by admin
     *@param $id = $class_teacher_id for which you want to approve
     *@param $term = term for which you want to approve
     *@return renders the view
     */
    public function getRecordedResultForApproval(Request $request)
    {
        set_time_limit(0);
        $result = new Result();
        $class_teacher_id = $request->class_teacher_id;
        $school_id = $this->getSchool()->id;
        $sess_id = $request->sess_id;
        $term_id = $request->term_id;
        $sub_term = $request->sub_term;
        $session = SSession::find($sess_id);

        //$request = request()->all();

        $class_details = ClassTeacher::with('c_class', 'level')->find($class_teacher_id);

        $subject_teachers = SubjectTeacher::with(['subject', 'staff.user'])->where(['class_teacher_id' => $class_teacher_id, 'school_id' => $school_id])->where('teacher_id', '!=', NULL)->get();

        $curriculum_level_group_id = $class_details->level->curriculum_level_group_id;
        $grades = $this->getLevelGrades($curriculum_level_group_id);
        $result_settings = $this->getResultSettings($curriculum_level_group_id);


        $options = [
            'class_teacher_id' => $class_teacher_id,
            'school_id' => $school_id,
            'sess_id' => $sess_id,
            'term' => $term_id,
            'sub_term' => $sub_term,
            'grades' => $grades,
            'result_settings' => $result_settings,
        ];
        $cant_approve = 0;
        if ($subject_teachers->isNotEmpty()) {
            $class_event = 0;
            foreach ($subject_teachers as $subject_teacher) :

                $results = Result::where(['sess_id' => $sess_id, 'term_id' => $term_id, 'school_id' => $school_id, 'subject_teacher_id' => $subject_teacher->id, 'class_teacher_id' => $class_teacher_id, 'result_status' => 'Applicable'])->get();

                $id = $subject_teacher->id;

                $assessment = $request->sub_term; //half or full
                if (isset($request->publish_result) && $request->publish_result == '1') {
                    //we publish all results here
                    foreach ($results as $result) {
                        $id = $subject_teacher->id;

                        $assessment = $request->sub_term; //half or full

                        $action = 'published';
                        if ($assessment === 'half') {
                            $result->midterm_status = 'published';
                        } else {
                            $result->fullterm_status = 'published';
                        }
                        $result->save();
                    }


                    // if ($class_event < 1) {
                    //     $request->class_teacher_id = $subject_teacher->class_teacher_id;
                    //     //we dont want to record save events...intead we want submit,approve, publish, disapprove etc
                    //     $event_action = ucwords($action) . " students " . ucwords($assessment) . "-Term result for " . $class_details->c_class->name;
                    //     //$request = json_encode($request);

                    //     $this->teacherStudentEventTrail($request, $event_action, 'class');
                    //     //$this->auditTrailEvent($request, $event_action);

                    //     $class_event++;
                    // }
                }
                $teacher_id = $subject_teacher->teacher_id;
                $subject_teacher->result_action_array = ['false', 'false', 'Not Submitted', 'Not Submitted'];
                if ($results->isNotEmpty()) {

                    $subject_teacher->result_action_array = $result->resultStatusAction($results[0]);
                }
                $submission_check = $subject_teacher->result_action_array;
                if ($submission_check != null) {
                    if ($assessment === 'half' && $submission_check[2] === 'Not Submitted') {
                        $cant_approve++;
                    } else if ($assessment === 'full' && $submission_check[3] === 'Not Submitted') {
                        $cant_approve++;
                    }
                }
                $subject_teacher_id = $subject_teacher->id;
                $teacher = new Teacher();
                $class_students = StudentsInClass::with(['student' => function ($query) {
                    $query->ActiveAndSuspended();
                }, 'student.user'])->where([
                    'class_teacher_id' => $subject_teacher->class_teacher_id,
                    'sess_id' => $sess_id,
                    //'term_id'=>$term_id,
                    'school_id' => $school_id
                ])->get();
                //$teacher->teacherSubjectStudents($subject_teacher, $sess_id, $term_id, $school_id, false);

                $students = [];
                if (!empty($class_students)) {
                    foreach ($class_students as $class_student) :
                        $student = $class_student->student;
                        if ($student) {
                            if ($student->studentship_status !== 'left') {
                                $student_id = $student->id;

                                $reg_no = $student->registration_no;
                                //check whether any record exits for this subject and student if not create one
                                $result_detail = $result->studentResult($sess_id, $student_id, $reg_no, $school_id, $term_id, $subject_teacher_id, $class_teacher_id, $teacher_id);

                                $result_detail = $result->updateResultDetails($result_detail, $result_settings);

                                // $mid_term = $result_detail->mid_term / 10;
                                // $ca1 = $result_detail->ca1;
                                // $ca2 = $result_detail->ca2;
                                // $ca3 = $result_detail->ca3;



                                $result_detail->test = $result->addCaScores($result_detail, $result_settings);

                                $student->result_detail = $result_detail;

                                // list($student_half_record,  $student_full_record) = $this->analyzeProgress($result_detail);
                                $students[] = $student;
                            }
                        }
                    endforeach;
                }
                $subject_teacher->students = $students;
            endforeach;
            //exit;

        }

        return response()->json(compact('subject_teachers', 'class_details', 'term_id', 'sub_term', 'session', 'cant_approve', 'result_settings'));
    }

    /**
     *This function fetches the results details for a particular subject
     *@param $subject_teacher_id =
     *@param $class_teacher_id
     *@param $term = term
     *@param $sub_term
     *@return renders the view
     */
    public function subjectResultDetails($subject_teacher_id, $class_teacher_id, $term_id, $sub_term)
    {
        $result = new Result();
        $school_id = $this->getSchool()->id;
        $sess_id = $this->getSession()->id;

        $class_details = ClassTeacher::find($class_teacher_id);

        $curriculum_level_group_id = $class_details->level->curriculum_level_group_id;
        $grades = $this->getLevelGrades($curriculum_level_group_id);
        $result_settings = $this->getResultSettings($curriculum_level_group_id);
        $options = [
            'class_teacher_id' => $class_teacher_id,
            'school_id' => $school_id,
            'sess_id' => $sess_id,
            'term' => $term_id,
            'sub_term' => $sub_term,
            'grades' => $grades,
            'result_settings' => $result_settings,
        ];
        $result_details = Result::where([
            'subject_teacher_id' => $subject_teacher_id,
            'class_teacher_id' => $class_teacher_id,
            'school_id' => $school_id,
            'sess_id' => $sess_id,
            'term_id' => $term_id,
            'result_status' => 'Applicable'
        ])
            ->orderBy('total', 'DESC')->get();


        if ($result_details != '[]') {
            $total_scores = [];
            $result_details_array = [];
            foreach ($result_details as $student_result) :

                list($test, $total, $result_grade, $color, $grade_point) = $result->processResultInfo($student_result, $grades, $result_settings, $options);

                $student_result->test = $test;
                $student_result->result_grade = $result_grade;
                $student_result->color = $color;
                $student_result->grade_point = $grade_point;


                $student_result->student = Student::find($student_result->student_id);


                $student_result->total = $total;

                $total_scores[] = $total;

            endforeach;
            $result_action_array = $result->resultStatusAction($result_details[0]);


            $subject_teacher = SubjectTeacher::find($subject_teacher_id);
            $teacher = Staff::find($subject_teacher->teacher_id)->user;

            $subject = $subject_teacher->subject;


            return $this->render(
                'results.subject_result_details',
                compact('result_details', 'total_scores', 'teacher', 'subject', 'class_details', 'subject_teacher_id', 'result_action_array', 'sub_term', 'average_array')
            );
        }
    }

    public function studentResultViewOption($id = NULL)
    {
        list($classes, $level_array, $term_array, $session_array) = $this->necessaryParams();
        $parent_view = FALSE;
        if ($id != NULL) {
            $parent_view = TRUE;
            $student = Student::find($id);
            return $this->render('results.view_student_result_form', compact('student', 'parent_view', 'term_array', 'session_array'));
        }
        $student = $this->getStudent();
        return $this->render('results.view_student_result_form', compact('student', 'parent_view', 'term_array', 'session_array'));
    }

    public function showResultRemark(Request $request)
    {
        $result = new Result();
        $class_teacher_id = $request->class_teacher_id;
        $class_teacher = ClassTeacher::find($class_teacher_id);

        $curriculum_level_group_id = $class_teacher->level->curriculum_level_group_id;
        $grades = $this->getLevelGrades($curriculum_level_group_id);
        $result_settings = $this->getResultSettings($curriculum_level_group_id);
        $school_id = $this->getSchool()->id;
        $sess_id = $request->sess_id;
        $term_id = $request->term_id;
        $sub_term = $request->sub_term;
        $remark_by = $request->remark_by;
        $action_by = "";
        if (isset($request->action_by)) {
            $action_by = $request->action_by;
        }

        $students_in_class = StudentsInClass::where([
            'class_teacher_id' => $class_teacher_id,
            'school_id' => $school_id,
            'sess_id' => $sess_id,
            'term_id' => $term_id
        ])->first();
        if ($students_in_class) {
            $students_id_array = explode('~', $students_in_class->student_ids);

            $head_teacher_remarks = [];
            $class_teacher_remarks = [];
            $class_averages = [];
            $student_averages = [];
            $class_average_colors = [];
            $student_average_colors = [];
            $name = [];
            $options = [
                'class_teacher_id' => $class_teacher_id,
                'school_id' => $school_id,
                'sess_id' => $sess_id,
                'term' => $term_id,
                'sub_term' => $sub_term,
                'grades' => $grades,
                'result_settings' => $result_settings,
            ];
            foreach ($students_id_array as $student_id) :
                $student = Student::find($student_id);
                $student_name = ucwords(strtolower($student->user->first_name . ' ' . $student->user->last_name));

                $name[$student_id] = $student_name;

                $student_results = Result::where(
                    [
                        'class_teacher_id' => $class_teacher_id,
                        'school_id' => $school_id,
                        'sess_id' => $sess_id,
                        'term_id' => $term_id,
                        'student_id' => $student_id,
                        'result_status' => 'Applicable',
                    ]
                )->get();

                //get the subject details so that we can generate automatic remarks


                if (!$student_results->isEmpty()) {

                    $total_scores = [];
                    $result_details_array = [];
                    foreach ($student_results as $student_result) :

                        $subject_name = SubjectTeacher::find($student_result->subject_teacher_id)->subject->code;

                        list($test, $total, $result_grade, $color, $grade_point) = $result->processResultInfo($student_result, $grades, $result_settings, $options);


                        $result_details_array[] = ['name' => $subject_name, 'grade' => $total];
                    endforeach;

                    list($student_results, $total_subject_class_average, $total_student_score, $result_count) = $result->processStudentResults($student_results, $options);



                    if ($result_count == 0) {
                        $class_average = 0;
                        $student_average = 0;
                        $class_average_color = "";
                        $student_average_color = "";
                    } else {
                        $class_average = sprintf("%01.1f", $total_subject_class_average / $result_count);
                        $student_average = sprintf("%01.1f", $total_student_score / $result_count);

                        list($class_average_result_grade, $class_average_color, $class_average_grade_point) = $result->resultGrade($class_average, $grades);

                        $class_average_color = $class_average_color;

                        list($student_average_result_grade, $student_average_color, $student_average_grade_point) = $result->resultGrade($student_average, $grades);
                        $student_average_color = $student_average_color;
                    }

                    //return $result_details_array;


                    $class_averages[$student_id] = $class_average;
                    $student_averages[$student_id] = $student_average;
                    $class_average_colors[$student_id] = $class_average_color;
                    $student_average_colors[$student_id] = $student_average_color;
                }

                $student_remark = Remark::where([
                    'class_teacher_id' => $class_teacher_id,
                    'school_id' => $school_id,
                    'sess_id' => $sess_id,
                    'term_id' => $term_id,
                    'sub_term' => $sub_term,
                    'student_id' => $student_id
                ])->first();
                if (!$student_remark) {

                    $student_remark = new Remark();
                    $student_remark->school_id = $school_id;
                    $student_remark->class_teacher_id = $class_teacher_id;
                    $student_remark->sess_id = $sess_id;
                    $student_remark->term_id = $term_id;
                    $student_remark->sub_term = $sub_term;
                    $student_remark->student_id = $student_id;
                    $student_remark->teacher_id = $students_in_class->classTeacher->teacher_id;
                    //this does the auto remark for each student
                    $student_remark->class_teacher_remark = ResultComment::getComment($student_name, $result_details_array, $student_average, 'class_teacher');
                    $student_remark->head_teacher_remark = ResultComment::getComment($student_name, $result_details_array, $student_average, 'head_teacher');
                    $student_remark->save();
                }

                $head_teacher_remarks[$student_id] = $student_remark->head_teacher_remark;
                $class_teacher_remarks[$student_id] = $student_remark->class_teacher_remark;
            endforeach;

            list($students, $all_subject_ids, $result_details_array, $result_averages) =
                $result->analyseStudentsResult($students_id_array, $options);

            //print_r($result_details_array);exit;
            if (!empty($result_averages)) {
                $result_averages =  $result_averages[0];
                //this sorts the total score in decending order
                arsort($result_averages, SORT_NUMERIC);
            }

            return $this->render('results.show_result_remark', compact('head_teacher_remarks', 'class_teacher_remarks', 'students_id_array', 'class_averages', 'student_averages', 'class_average_colors', 'student_average_colors', 'sess_id', 'class_teacher_id', 'term_id', 'sub_term', 'remark_by', 'name', 'result_averages', 'action_by'));
        } //end if

    } //end function

    public function giveStudentRemark(Request $request)
    {
        $class_teacher_id = $request->class_teacher_id;
        $school_id = $this->getSchool()->id;
        $sess_id = $request->sess_id;
        $term_id = $request->term_id;
        $sub_term = $request->sub_term;
        $remark_by = $request->remark_by;
        $student_id = $request->student_id;
        $remark = $request->remark;

        $student_remark = Remark::where([
            'class_teacher_id' => $class_teacher_id,
            'school_id' => $school_id,
            'sess_id' => $sess_id,
            'term_id' => $term_id,
            'sub_term' => $sub_term,
            'student_id' => $student_id
        ])->first();

        if ($remark_by == 'class_teacher') {
            $student_remark->class_teacher_remark = $remark;
            $student_remark->class_teacher_status = 'custom';
        } else {
            $student_remark->head_teacher_remark = $remark;
            $student_remark->head_teacher_status = 'custom';
        }
        $student_remark->save();
    }
}