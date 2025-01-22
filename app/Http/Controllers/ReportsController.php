<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
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
use App\Laravue\JsonResponse;
use App\Models\FeePaymentMonitor;
use App\Models\School;
use App\Models\Staff;
use App\Models\Term;

class ReportsController extends Controller
{
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
            return response()->json([], Response::HTTP_FORBIDDEN);
        }

        return $this->render('reports::index', compact('report_category'));
    }




    public function displayReportChart(Request $request)
    {
        if (isset($request->category) && $request->category != "") {
            $category = $request->category;

            switch ($category) {
                case 'population':
                    return $this->populationReport();
                    break;
                case 'admission':
                    return $this->admissionReport();
                    break;
                case 'attendance':
                    return $this->attendanceReport($request);
                    break;
                case 'debtors':
                    return $this->debtorsReport();
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
                case 'teacher_subject_performance':
                    return $this->teacherSubjectsPerformanceReport();
                    break;
                default:
                    # code...
                    break;
            }
        }

        return response()->json([], Response::HTTP_NOT_FOUND);
    }


    public function populationReport()
    {
        $request = request()->all();
        if (isset($request['lga']) && $request['lga'] != null) {
            $lga = get_object_vars(json_decode($request['lga']));
            $lga_id = $lga['id'];
            $lga_name = $lga['name'];
            $lgaPopulationDataArray = $this->fetchLGAPopulation($lga_id);
            $title = 'Population Reports for Schools in ' . $lga_name;
            $subtitle = 'Report showing Gender Count of Students and Staff';

            $schools = $lga['schools'];
            $maleStudentData = [];
            $femaleStudentData = [];
            $maleStaffData = [];
            $femaleStaffData = [];
            $dataLabels = [
                'enabled' => true,
                'rotation' => -90,
                'color' => '#FFFFFF',
                'align' => 'center',
                //format: '{point.y:.1f}', // one decimal
                'y' => 25, // 10 pixels down from the top
                'style' => [
                    'fontSize' => '10px',
                    'fontFamily' => 'Verdana, sans-serif'
                ]
            ];
            foreach ($schools as $school) :
                list($maleStudents, $femaleStudents, $maleStaff, $femaleStaff) = $this->fetchSchoolPopulation($school->id);

                $maleStudentData[] = [
                    'name' => $school->name,
                    'y' => (int) $maleStudents,
                    //'drilldown' => $school->name . '_maleStudent',

                ];
                $femaleStudentData[] = [
                    'name' => $school->name,
                    'y' => (int) $femaleStudents,
                    //'drilldown' => $level->level . '_absent',

                ];
                $maleStaffData[] = [
                    'name' => $school->name,
                    'y' => (int) $maleStaff,
                    //'drilldown' => $level->level . '_absent',

                ];
                $femaleStaffData[] = [
                    'name' => $school->name,
                    'y' => (int) $femaleStaff,
                    //'drilldown' => $level->level . '_absent',

                ];
            endforeach;
            $series = [
                [
                    'name' => 'Male Students',
                    //'colorByPoint' => true, //array format
                    'data' => $maleStudentData,
                    'stack' => 'student',
                    'color' => '#00c0ef ',
                    //'dataLabels' => $dataLabels
                ],
                [
                    'name' => 'Female Students',
                    'data' => $femaleStudentData, //array format
                    'stack' => 'student',
                    'color' => '#DC143C ',
                    //'dataLabels' => $dataLabels
                ],
                [
                    'name' => 'Male Staff',
                    'data' => $maleStaffData, //array format
                    'stack' => 'staff',
                    'color' => '#00a65a',
                    //'dataLabels' => $dataLabels
                ],
                [
                    'name' => 'Female Staff',
                    'data' => $femaleStaffData, //array format
                    'stack' => 'staff',
                    'color' => '#f39c12',
                    //'dataLabels' => $dataLabels
                ],
                [
                    'type' =>  'pie',
                    'name' => 'Total Population',
                    'data' => [
                        [
                            'name' => 'Total Male Student',
                            'y' => $lgaPopulationDataArray[0],
                            'color' => '#00c0ef' // Jane's color
                        ],
                        [
                            'name' => 'Total Female Student',
                            'y' => $lgaPopulationDataArray[1],
                            'color' => '#DC143C' // Jane's color
                        ],
                        [
                            'name' => 'Total Male Staff',
                            'y' => $lgaPopulationDataArray[2],
                            'color' => '#00a65a' // Jane's color
                        ],
                        [
                            'name' => 'Total Female Staff',
                            'y' => $lgaPopulationDataArray[3],
                            'color' => '#f39c12' // Jane's color
                        ],
                    ],
                    'center' => [50, 10],
                    'size' => 150,
                    'showInLegend' => false,
                    'dataLabels' => [
                        'enabled' => false
                    ]
                ]

            ];

            return response()->json(compact(/*'chartLabel', 'presentData', 'absentData',*/'lgaPopulationDataArray', 'lga_name', 'series', 'title', 'subtitle'), 200);
        }
        // if (isset($request['school']) && $request['school'] != null) {
        //     $school = get_object_vars(json_decode($request['school']));
        //     $school_id = $school['id'];

        //     list($maleStudents, $femaleStudents, $maleStaff, $femaleStaff) = $this->fetchSchoolPopulation($school_id);
        // }

    }
    private function fetchLGAPopulation($lga_id)
    {
        $maleStudents = Student::join('schools', 'schools.id', 'students.school_id')
            ->join('local_government_areas', 'local_government_areas.id', 'schools.lga_id')->join('users', 'users.id', 'students.user_id')
            ->where(['schools.lga_id' => $lga_id, 'users.gender' => 'male'])->count();

        $femaleStudents = Student::join('schools', 'schools.id', 'students.school_id')
            ->join('local_government_areas', 'local_government_areas.id', 'schools.lga_id')->join('users', 'users.id', 'students.user_id')
            ->where(['schools.lga_id' => $lga_id, 'users.gender' => 'female'])->count();

        $maleStaff = Staff::join('schools', 'schools.id', 'staff.school_id')
            ->join('local_government_areas', 'local_government_areas.id', 'schools.lga_id')
            ->join('users', 'users.id', 'staff.user_id')
            ->where(['schools.lga_id' => $lga_id, 'users.gender' => 'male'])->count();
        $femaleStaff = Staff::join('schools', 'schools.id', 'staff.school_id')
            ->join('local_government_areas', 'local_government_areas.id', 'schools.lga_id')
            ->join('users', 'users.id', 'staff.user_id')
            ->where(['schools.lga_id' => $lga_id, 'users.gender' => 'female'])->count();

        return array($maleStudents, $femaleStudents, $maleStaff, $femaleStaff);
    }
    private function fetchSchoolPopulation($school_id)
    {
        $maleStudents = Student::join('users', 'users.id', 'students.user_id')
            ->where(['school_id' => $school_id, 'users.gender' => 'male'])->count();

        $femaleStudents = Student::join('users', 'users.id', 'students.user_id')
            ->where(['school_id' => $school_id, 'users.gender' => 'female'])->count();

        $maleStaff = Staff::join('users', 'users.id', 'staff.user_id')
            ->where(['school_id' => $school_id, 'users.gender' => 'male'])->count();

        $femaleStaff = Staff::join('users', 'users.id', 'staff.user_id')
            ->where(['school_id' => $school_id, 'users.gender' => 'female'])->count();

        return array($maleStudents, $femaleStudents, $maleStaff, $femaleStaff);
    }
    public function admissionReport()
    {
        $request = request()->all();


        if (isset($request['school_id']) && $request['school_id'] != null) {
            //$school = get_object_vars(json_decode($request['school']));
            $school_id = $request['school_id'];
        } else {
            $school_id = $this->getSchool()->id;
        }

        $sess_id = $this->getSession()->id;
        $term_id = $this->getTerm()->id;

        $levels = $this->getLevels(); //Level::orderBy('level', 'ASC')->get();
        $all_sessions = SSession::orderBy('id', 'DESC')->get();

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


        $categories = [];
        $male = [];
        $female = [];
        $total = [];
        $dataLabels = [
            'enabled' => true,
            // 'rotation' => 0,
            'color' => '#FFFFFF',
            'align' => 'center',
            //format: '{point.y:.1f}', // one decimal
            // 'y' => 25, // 10 pixels down from the top
            'style' => [
                'fontSize' => '10px',
                'fontFamily' => 'Verdana, sans-serif'
            ]
        ];

        $drilldown_male_series = [];
        $drilldown_female_series = [];
        $drilldown_total_series = [];
        foreach ($levels as $level) :
            $categories[] = $level->level;
            $male_count = Student::join('users', 'users.id', 'students.user_id')
                ->where(['school_id' => $school_id, 'gender' => 'male', 'admission_sess_id' => $admission_sess_id, 'level_admitted' => $level->id])->count();
            $female_count = Student::join('users', 'users.id', 'students.user_id')
                ->where(['school_id' => $school_id, 'gender' => 'female', 'admission_sess_id' => $admission_sess_id, 'level_admitted' => $level->id])->count();
            $total_count = Student::where(['school_id' => $school_id, 'admission_sess_id' => $admission_sess_id, 'level_admitted' => $level->id])->count();
            $male[] = [
                'name' => $level->level,
                'y' => (int) $male_count,
                'drilldown' => $level->level . '_male',

            ];

            $female[] = [
                'name' => $level->level,
                'y' => (int) $female_count,
                'drilldown' => $level->level . '_female',

            ];

            $total[] = [
                'name' => $level->level,
                'y' => (int) $total_count,
                'drilldown' => $level->level . '_total',

            ];
            $drilldown_series_males_in_class = [];
            $drilldown_series_females_in_class = [];
            $drilldown_series_total_in_class = [];
            foreach ($level->classTeachers as $class_teacher) {
                $males_in_class = $class_teacher->studentsInClass()->join('students', 'students_in_classes.student_id', '=', 'students.id')->join('users', 'users.id', 'students.user_id')->where(['students.school_id' => $school_id, 'gender' => 'male', 'students.admission_sess_id' => $admission_sess_id, 'students.level_admitted' => $level->id])->count();

                $females_in_class = $class_teacher->studentsInClass()->join('students', 'students_in_classes.student_id', '=', 'students.id')->join('users', 'users.id', 'students.user_id')->where(['students.school_id' => $school_id, 'gender' => 'female', 'students.admission_sess_id' => $admission_sess_id, 'students.level_admitted' => $level->id])->count();
                $total_in_class = $males_in_class + $females_in_class;

                $drilldown_series_males_in_class[] = [$class_teacher->c_class->name, (int) $males_in_class];
                $drilldown_series_females_in_class[] = [$class_teacher->c_class->name, (int) $females_in_class];
                $drilldown_series_total_in_class[] = [$class_teacher->c_class->name, (int) $total_in_class];
            }
            $drilldown_male_series[] =    [
                "name" => 'Male',
                "id" => $level->level . '_male',
                "data" => $drilldown_series_males_in_class,
                //'dataLabels' => $dataLabels
            ];
            $drilldown_female_series[] =    [
                "name" => 'Female',
                "id" => $level->level . '_female',
                "data" =>  $drilldown_series_females_in_class,
                //'dataLabels' => $dataLabels
            ];
        // $drilldown_total_series[] =    [
        //     "name" => 'Total',
        //     "id" => $level->level . '_total',
        //     "data" =>  $drilldown_series_total_in_class,
        //     'dataLabels' => $dataLabels
        // ];
        endforeach;

        $drilldown_series = array_merge($drilldown_male_series, $drilldown_female_series, $drilldown_total_series);
        $series = [
            [
                'name' => 'Male',
                'data' => $male, //array format
                // 'color' => '#00c0ef',
                'dataLabels' => $dataLabels
            ],
            [
                'name' => 'Female',
                'data' => $female, //array format
                // 'color' => '#DC143C',
                'dataLabels' => $dataLabels
            ],
            // [
            //     'name' => 'Total',
            //     'data' => $total, //array format
            //     'color' => '#000000',
            //     'dataLabels' => $dataLabels
            // ],
        ];
        // $school = School::find($school_id);
        return response()->json([

            'categories'    => $categories,
            'series'      => $series,
            // 'title' => $school->name . ', ' . $school->lga->name,
            'title' => 'Admissions Chart for ' . $selected_session->name . ' Academic Session',
            'subtitle' => 'Click the columns to view report for each class',
            'selected_session' => $selected_session,
            'all_sessions' => $all_sessions,
            'admission_sess_id' => $admission_sess_id,
            'chart_only' => $chart_only,
            'drilldown_series' => $drilldown_series
        ], 200);
        //return $this->render('reports.admission', compact('levels', 'selected_session', 'all_sessions', 'admission_sess_id', 'chart_only'));
    }


    public function studentPerformanceReport()
    {
        $result = new Result();
        $request = request()->all();

        if (isset($request['school_id']) && $request['school_id'] != null) {
            //$school = get_object_vars(json_decode($request['school']));
            $school_id = $request['school_id'];
        } else {
            $school_id = $this->getSchool()->id;
        }
        $sess_id = $this->getSession()->id;
        $term_id = $this->getTerm()->id;


        $grades = $this->getGrades();
        $chart_only = "false";
        if (isset($request['chart_only'])) {
            $chart_only = "true";
        }

        $all_sessions = SSession::orderBy('id', 'DESC')->get();

        $all_classes = ClassTeacher::where('school_id', $school_id)->orderBy('id')->get();
        $class_teacher_id = '';
        if ($all_classes->isNotEmpty()) {
            $class_teacher_id = $all_classes[0]->id;
        }

        $hide_selection = '0';
        if (isset($request['sess_id'], $request['class_teacher_id'], $request['term_id']) && $request['sess_id'] != "" && $request['class_teacher_id'] != "" && $request['term_id'] != "") {

            $sess_id = $request['sess_id'];
            $class_teacher_id = $request['class_teacher_id'];
            $term_id = $request['term_id'];
        }
        if (isset($request['hide_selection'])) {
            $hide_selection = $request['hide_selection'];
        }

        $class_teacher = ClassTeacher::find($class_teacher_id);
        $curriculum_level_group_id = $class_teacher->level->levelGroup->id;
        $grades = $this->getLevelGrades($curriculum_level_group_id);
        $result_settings = $this->getResultSettings($curriculum_level_group_id);
        $options = [
            'class_teacher_id' => $class_teacher_id,
            'school_id' => $school_id,
            'sess_id' => $sess_id,
            'term' => $term_id,
            'sub_term' => 'full',
            'grades' => $grades,
            'result_settings' => $result_settings,
        ];

        $subject_teachers = SubjectTeacher::where('class_teacher_id', $class_teacher_id)->where('teacher_id', '!=', NULL)->get();

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



        $data = [];
        $drilldown_series = [];
        $gender_performance_categories = [];
        $gender_performance_series_male_data = [];
        $gender_performance_series_female_data = [];
        $dataLabels = [
            'enabled' => true,
            //'rotation' => -90,
            'color' => '#FFFFFF',
            'align' => 'center',
            //format: '{point.y:.1f}', // one decimal
            //'y' => 25, // 10 pixels down from the top
            'style' => [
                'fontSize' => '10px',
                'fontFamily' => 'Verdana, sans-serif'
            ]
        ];
        foreach ($subject_teachers as $subject_teacher) :


            if ($term_id == 0) {
                $results = Result::where(['school_id' => $school_id, 'sess_id' => $sess_id, 'subject_teacher_id' => $subject_teacher->id])->where('comments', "!=", NULL)->get();
            } else {
                $results = Result::where(['school_id' => $school_id, 'sess_id' => $sess_id, 'term_id' => $term_id, 'subject_teacher_id' => $subject_teacher->id])->where('comments', "!=", NULL)->get();
            }



            list($subject_class_average, $subject_highest_score, $subject_lowest_score, $male_average, $female_average, $subject_totals) = $result->subjectStudentPerformance($results, $grades, $result_settings, $options);

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


            $drilldown = null;
            $y = $subject_teacher->subject_class_average;
            if ($y > 0) {

                $drilldown = $subject_teacher->subject->name;

                list($grade, $color, $grade_point) = $result->resultGrade($y, $grades);
                $data[] = [
                    'name' => $subject_teacher->subject->name,
                    'y' => (float) $y,
                    'drilldown' => $drilldown,
                    // 'color' => $color

                ];

                if ($subject_teacher->subject_class_average > 0) {

                    $drilldown_series_data = [];
                    foreach ($subject_teacher->performance as $grade => $value) {
                        $drilldown_series_data[] = [$grade, $value];
                    }

                    $drilldown_series[] =    [
                        "name" => $subject_teacher->subject->name,
                        "id" => $subject_teacher->subject->name,
                        "data" => $drilldown_series_data,




                    ];
                }

                $gender_performance_categories[] = $subject_teacher->subject->name;

                $gender_performance_series_male_data[] = (float) $subject_teacher->male_average;
                $gender_performance_series_female_data[] = (float) $subject_teacher->female_average;
            }
        endforeach;

        $gender_performance_series = [
            [
                'name' => 'Male',
                'data' => $gender_performance_series_male_data, //array format
                // 'color' => '#0073b7',
                'dataLabels' => $dataLabels
            ],
            [
                'name' => 'Female',
                'data' => $gender_performance_series_female_data, //array format
                // 'color' => '#f012be',

                'dataLabels' => $dataLabels
            ],

        ];

        //return $drilldown_series;
        $term_array = [
            '1' => 'First Term',
            '2' => 'Second Term',
            '3' => 'Third Term',


        ];
        $selected_classes = [];
        $selected_session = SSession::find($sess_id)->name;

        $selected_class_teacher = ClassTeacher::find($class_teacher_id);

        $selected_class = '';
        $level = '';
        if ($selected_class_teacher) {
            $selected_class = $selected_class_teacher->c_class->name;
            $level = $selected_class_teacher->level;
        }
        $levels = $this->getLevels();

        $selected_term = $term_array[$term_id];

        $series = [
            [
                'name' => 'Subjects Average',
                'colorByPoint' => true, //array format
                'data' => $data,
                //'color'=> '#0073b7',
                'dataLabels' => $dataLabels
            ],

        ];
        $school = School::find($school_id);


        $subtitle = 'Click the columns to view grade count for each subject';
        $title =  $selected_class . ' Students Academic Performance';

        $gender_performance_title = $selected_class . " Gender Performance Comparison Chart";
        $gender_performance_subtitle = "Report comparing average between Male and Female in each subject for $selected_term in $selected_session Academic Session";


        return response()->json(compact('subject_teachers', 'all_sessions', 'all_classes', 'term_array', 'class_teacher_id', 'sess_id', 'term_id', 'performance', 'performance_color', 'selected_session', 'selected_class', 'selected_term', 'chart_only', 'hide_selection', 'series', 'subtitle', 'title', 'levels', 'level', 'selected_classes', 'drilldown_series', 'gender_performance_series', 'gender_performance_categories', 'gender_performance_title', 'gender_performance_subtitle'), 200);
    }

    public function teacherSubjectsPerformanceReport()
    {
        $request = request()->all();
        $result = new Result();
        $school_id = $this->getSchool()->id;
        $staff_id = $this->getStaff()->id;

        $sess_id = 4; //$this->getSession()->id;
        $term_id = 2; //$this->getTerm()->id;
        if (isset($request['sess_id']) && $request['sess_id'] !== null) {
            //$school = get_object_vars(json_decode($request['school']));
            $sess_id = $request['sess_id'];
        }
        if (isset($request['term_id']) && $request['term_id'] !== null) {
            //$school = get_object_vars(json_decode($request['school']));
            $term_id = $request['term_id'];
        }
        $results = $result->analyzeTeacherTermlySubjectPerformance($staff_id, $school_id, $sess_id, $term_id);

        $subject_names = [];
        $data = [];
        foreach ($results as $result) {
            $subject_names[] = $result->subjectTeacher->subject->code . ' (' . $result->classTeacher->c_class->name . ')';
            $data[] = sprintf("%01.1f", $result->average);
        }
        $series = [[
            'name' => 'Subject Average',
            'data' => $data
        ]];
        $all_sessions = SSession::orderBy('id', 'DESC')->get();
        $term_array = [
            '1' => 'First Term',
            '2' => 'Second Term',
            '3' => 'Third Term'

        ];
        $selected_session = SSession::find($sess_id)->name;
        $selected_term = Term::find($term_id)->name;
        return $this->render(compact('subject_names', 'series', 'all_sessions', 'term_array', 'selected_session', 'selected_term', 'term_id', 'sess_id'));
    }

    public function attendanceReport(Request $request)
    {
        $date = getDateFormat($request->date);
        if (isset($request->school_id)) {
            $school_id = $request->school_id;
        } else {
            $school_id = $this->getSchool()->id;
        }

        $school = School::find($school_id);
        $school_name = $school->name . ', ' . $school->lga->name;
        $title = $school_name . '<br> Attendance Chart for ' . getDateFormatWords($date);
        $subtitle = 'Click on a column to see attendance for each class';
        $levels = $this->getLevels();
        $presentData = [];
        $absentData = [];
        $chartLabel = [];
        //$data = [];
        //$drilldown_series = [];
        $drilldown_present_series = [];
        $drilldown_absent_series = [];
        $dataLabels = [
            'enabled' => true,
            'rotation' => -90,
            'color' => '#FFFFFF',
            'align' => 'center',
            //format: '{point.y:.1f}', // one decimal
            'y' => 25, // 10 pixels down from the top
            'style' => [
                'fontSize' => '10px',
                'fontFamily' => 'Verdana, sans-serif'
            ]
        ];
        foreach ($levels as $level) :
            $total_present = ClassAttendance::where(['level_id' => $level->id, 'school_id' => $school_id])
                ->whereDate('date', '=', $date)->sum('total_present');
            $total_absent = ClassAttendance::where(['level_id' => $level->id, 'school_id' => $school_id])
                ->whereDate('date', '=', $date)->sum('total_absent');

            $presentData[] = [
                'name' => $level->level,
                'y' => (int) $total_present,
                'drilldown' => $level->level . '_present',

            ];
            $absentData[] = [
                'name' => $level->level,
                'y' => (int) $total_absent,
                'drilldown' => $level->level . '_absent',

            ];
            //if ($total_present > 0) {

            $drilldown_series_present_data = [];
            $drilldown_series_absent_data = [];

            foreach ($level->classTeachers as $class_teacher) {
                $class_present = ClassAttendance::where('class_teacher_id', $class_teacher->id)
                    ->whereDate('date', '=', $date)->sum('total_present');
                $class_absent = ClassAttendance::where('class_teacher_id', $class_teacher->id)
                    ->whereDate('date', '=', $date)->sum('total_absent');
                $drilldown_series_present_data[] = [$class_teacher->c_class->name, (int) $class_present];
                $drilldown_series_absent_data[] = [$class_teacher->c_class->name, (int) $class_absent];
            }

            $drilldown_present_series[] =    [

                "name" => 'Present',
                "id" => $level->level . '_present',
                "data" => $drilldown_series_present_data,
                'dataLabels' => $dataLabels


            ];
            $drilldown_absent_series[] =    [

                "name" => 'Absent',
                "id" => $level->level . '_absent',
                "data" =>  $drilldown_series_absent_data,
                'dataLabels' => $dataLabels

            ];
        //}

        // $presentData[] = $total_present;
        // $absentData[] = $total_absent;
        // $chartLabel[] = $level->level;
        endforeach;
        $drilldown_series = array_merge($drilldown_present_series, $drilldown_absent_series);
        $series = [
            [
                'name' => 'Present',
                //'colorByPoint' => true, //array format
                'data' => $presentData,
                'color' => '#0073b7',
                'dataLabels' => $dataLabels
            ],
            [
                'name' => 'Absent',
                'data' => $absentData, //array format
                'color' => '#f012be',
                'dataLabels' => $dataLabels
            ],

        ];
        return response()->json(compact(/*'chartLabel', 'presentData', 'absentData',*/'school_name', 'drilldown_series', 'series', 'title', 'subtitle'), 200);
    }

    // public function attendanceReport()
    // {
    //     $class_attendance_obj = new ClassAttendance();
    //     $teacher_obj = new Teacher();
    //     $request = request()->all();
    //     $school_id = $this->getSchool()->id;
    //     $sess_id = $this->getSession()->id;
    //     $term_id = $this->getTerm()->id;

    //     $date = date('Y-m', strtotime(todayDate())); //today

    //     $all_classes = ClassTeacher::where('school_id', $school_id)->orderBy('id')->get();

    //     $classes = [];
    //     foreach ($all_classes as $class) {
    //         $classes[] = ['name' => $class->c_class->name, 'id' => $class->id];
    //     }
    //     $attendance_class_id = $all_classes[0]->id;

    //     $chart_only = "false";
    //     if (isset($request['chart_only'])) {
    //         $chart_only = "true";
    //     }

    //     if (isset($request['date'], $request['attendance_class_id']) && $request['date'] != "" && $request['attendance_class_id'] != "") {

    //         $date = $request['date'];
    //         $attendance_class_id = $request['attendance_class_id'];
    //     }
    //     $day = (int) date('d', strtotime($date));
    //     $month = date('m', strtotime($date));
    //     $year = date('Y', strtotime($date));

    //     $no_of_days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);

    //     $options = array(
    //         'option' => 'class', //$option,
    //         'toDate' => $date,
    //         'id' => $attendance_class_id
    //     );

    //     list($marked_month_attendances, $marked_today, $marked_student_array, $attendance_id) = $class_attendance_obj->markedAttendance($options);

    //     //fetch all students in class
    //     list($student_ids_arr, $students) = $teacher_obj->teacherstudentsInClass($attendance_class_id, $sess_id, $term_id, $school_id);

    //     $attendances =  $marked_month_attendances;
    //     $total_present = 0;
    //     $total_absent = 0;
    //     $total_students = count($student_ids_arr);

    //     $categories = [];
    //     $present = [];
    //     $absent = [];
    //     $total = [];
    //     $average = [];


    //     for ($i = 1; $i <= $no_of_days_in_month; $i++) {

    //         $categories[] =  "$i";

    //         $found = false;
    //         if ($attendances != '[]') {
    //             foreach ($attendances as $attendance) {

    //                 $present_students = 0;
    //                 $absent_students = 0;
    //                 if ($attendance->student_ids != null && $attendance->student_ids != "") {
    //                     $present_students = count(explode('~', $attendance->student_ids));
    //                 }

    //                 if ($attendance->absent_students != null && $attendance->absent_students != "") {
    //                     $absent_students = count(explode('~', $attendance->absent_students));
    //                 }

    //                 if ((int) date('d', strtotime($attendance->date)) == $i) {

    //                     //$total_students = $present_students + $absent_students;

    //                     $total_present += $present_students;
    //                     $total_absent += $absent_students;

    //                     $present[] = $present_students;
    //                     $absent[] = $absent_students;
    //                     $total[] = $total_students;
    //                     $average[] .= ($total_students / 2);

    //                     $found = true;
    //                 }
    //             }
    //             if (!$found) {
    //                 $present[] = null;
    //                 $absent[] = null;
    //                 $total[] = null;
    //                 $average[] = null;
    //             }
    //         }
    //     }
    //     $percentage_present = 0;
    //     $percentage_absent = 0;
    //     if ($total_students != 0) {
    //         $percentage_present = $total_present / 100 * $total_students;
    //         $percentage_absent = $total_absent / 100 * $total_students;
    //     }

    //     $class = ClassTeacher::find($attendance_class_id)->c_class;

    //     //$categories = [$categories_string];
    //     $series = [
    //         [
    //             'type' => 'column',

    //             'name' => 'Present',
    //             'data' => $present, //array format

    //         ],
    //         [
    //             'type' => 'column',
    //             'name' => 'Absent',
    //             'data' => $absent, //array format

    //         ],
    //         /*[
    //                     'type'=> 'pie',
    //                     'name'=>'Percentage Attendance',
    //                     'data'=> [
    //                         [
    //                             'name'=> 'Present',
    //                             'y'=> $percentage_present,
    //                             'color'=> '#063' // Jane's color
    //                         ],
    //                         [
    //                             'name'=> 'Absent',
    //                             'y'=> $percentage_absent,
    //                             'color'=> '#910000' // John's color
    //                         ]
    //                     ],

    //                     'center'=> [30, 0],
    //                     'size'=> 100,
    //                     'showInLegend'=> false,
    //                     'dataLabels'=> [
    //                         'enabled'=> false
    //                     ]

    //                 ],*/
    //     ];

    //     return response()->json([

    //         'categories'    => $categories,
    //         'series'      => $series,
    //         'subtitle' => 'Report for Month of ' . date('F, Y', strtotime($date)),
    //         'title' => $class->name . ' Attendance Report',
    //         'classes' => $classes,
    //         'selected_class' => ['name' => $class->name, 'id' => $attendance_class_id],
    //     ], 200);

    //     //return $this->render('reports.attendance', compact('attendances', 'class', 'percentage_present', 'percentage_absent', 'all_classes', 'no_of_days_in_month', 'date', 'attendance_class_id', 'students', 'marked_student_array', 'day', 'chart_only'));
    // }

    public function debtorsReport()
    {
        $request = request()->all();
        $school_id = $this->getSchool()->id;
        $sess_id = $this->getSession()->id;
        $term_id = $this->getTerm()->id;
        if (isset($request['sess_id'], $request['term_id']) && $request['sess_id'] !== '' && $request['term_id'] !== '') {
            $sess_id = $request['sess_id'];
            $term_id = $request['term_id'];
        }

        $levels = $this->getLevels(); //Level::orderBy('level', 'ASC')->get();

        $categories = [];
        $total = [];
        $labels = [];
        foreach ($levels as $level) :
            $categories[] = $level->level;

            $debt = FeePaymentMonitor::groupBy('level_id')
                ->where([
                    'school_id' => $school_id,
                    'sess_id' => $sess_id,
                    'term_id' => $term_id,
                    'level_id' => $level->id
                ])
                ->select(\DB::raw('SUM(total_fee - amount_paid) as total_debt'))
                ->first();
            if ($debt) {
                //if ($debt->total_debt > 0) {

                $labels[] = $level->level;
                // $total[] = (int) $debt->total_debt;

                $total[] = [
                    'x' => $level->level,
                    'y' => (int) ($debt->total_debt) ? $debt->total_debt : 0,

                ];
                // }
            }
        endforeach;
        // for pie chart
        // $series = $total;

        // for bar chart
        $series = [
            [
                'name' => 'Total Debts',
                'data' => $total, //array format
            ],
        ];
        $selected_session = SSession::find($sess_id);
        $selected_term = Term::find($term_id);
        // $school = School::find($school_id);
        return $this->render([
            'categories'    => $categories,
            'series'      => $series,
            'labels' => $labels,
            'title' => 'Debtors Report for ' . $selected_term->name . ' Term,' . ' ' . $selected_session->name . ' Session',
        ], 200);
        //return $this->render('reports.admission', compact('levels', 'selected_session', 'all_sessions', 'admission_sess_id', 'chart_only'));
    }


    public function financialReport()
    {
        $request = request()->all();

        $school_id = $this->getSchool()->id;
        $sess_id = $this->getSession()->id;
        $term_id = $this->getTerm()->id;
        $sess_id = $request['sess_id'];

        // $day = (int) (date('d', strtotime($date)));
        // $month_int = date('m', strtotime($date));
        // $year = (int) (date('Y', strtotime($date)));

        // $no_of_days_in_month = cal_days_in_month(CAL_GREGORIAN, $month_int, $year);

        // $month_str = date('F', strtotime($date));

        $income_n_expenses = IncomeAndExpense::selectRaw('SUM(amount) as sum, status, term_id')
            ->groupBy('term_id', 'status')
            ->where('deletable', '0')
            ->where(['school_id' => $school_id, 'sess_id' => $sess_id])
            ->get();
        $income_data = [0, 0, 0];
        $expenses_data = [0, 0, 0];
        $total_income = 0;
        $total_expenses = 0;
        foreach ($income_n_expenses as $income_n_expense) {

            $term_id = $income_n_expense->term_id;
            $sum = $income_n_expense->sum;
            if ($income_n_expense->status === 'income') {
                $income_data[$term_id - 1] = $sum;
                $total_income += $sum;
            }
            if ($income_n_expense->status === 'expenses') {
                // $expenses_data[$term_id - 1] = $sum;
                $expenses_data[$term_id - 1] = $sum * -1; // we want to make the expenses negative
                $total_expenses += $sum;
            }
        }
        $bar_chart_series = [
            ['name' => 'Income', 'data' => $income_data],
            ['name' => 'Expenditure', 'data' => $expenses_data]
        ];
        $pie_chart_series = [$total_income, $total_expenses];
        $profit = $total_income - $total_expenses;


        return $this->render(compact('bar_chart_series', 'pie_chart_series', 'profit', 'total_income', 'total_expenses'));
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
        list($student_ids_arr,$students) = $teacher_obj->teacherstudentsInClass($attendance_class_id,$sess_id,$term_id,$school_id);

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



        return $this->render('reports.attendance', compact('attendances', 'class', 'percentage_present', 'percentage_absent', 'all_classes', 'no_of_days_in_month', 'date', 'attendance_class_id', 'students', 'marked_student_array', 'day', 'chart_only'));
    }*/

    public function parentViewStudentAcademicChart(Request $request, Result $result)
    {
        $user = $this->getUser();
        $request = request()->all();
        $all_sessions = SSession::get();
        $school_id = $this->getSchool()->id;
        $sess_id = $this->getSession()->id;
        $school_id = $this->getSchool()->id;
        if (isset($request['sess_id']) && $request['sess_id'] != "") {

            $sess_id = $request['sess_id'];
        }
        $selected_session = SSession::find($sess_id)->name;
        $grades = $this->getGrades(); //Grade::all();
        //check whether the user has parent role
        if ($user->hasRole('parent')) {

            $guardian = $this->getGuardian();
            $ward_ids = $guardian->ward_ids;

            $ward_id_array = explode('~', substr($ward_ids, 1));



            $student_average = [];
            foreach ($ward_id_array as $key => $student_id) :
                if ($student_id != "") {
                    # code...

                    //fetch the academic detail of this student for the selected session
                    list($student_termly_performance, $class_termly_average) = $this->fetchStudentAcademicPerformance($student_id, $sess_id, $school_id, $grades);

                    $student = Student::find($student_id);
                    $student_name = $student->user->first_name . ' ' . $student->user->last_name;
                    $student_average[$student_name] = $student_termly_performance;
                }
            endforeach;


            //return $student_average;

            return $this->render('reports.parent_view_student_performance', compact('student_average', 'selected_session', 'all_sessions', 'sess_id'));
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
            $grades = $this->getGrades(); //Grade::all();

            $student_average = [];
            list($student_termly_performance, $class_termly_average) = $this->fetchStudentAcademicPerformance($student_id, $sess_id, $school_id, $grades);

            $student_name = $student->user->first_name . ' ' . $student->user->last_name;
            $student_average[$student_name] = $student_termly_performance;
            $student_average['Class Average'] = $class_termly_average;

            //return $student_average;

            return $this->render('reports.student_academic_report', compact('student_average', 'selected_session', 'all_sessions', 'sess_id', 'student', 'student_name'));
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
                $class_teacher = $student_class->classTeacher;
                $class_teacher_id = $student_class->class_teacher_id;
                $curriculum_level_group_id = $class_teacher->level->levelGroup->id;

                $result_settings = $this->getResultSettings($curriculum_level_group_id);
                $options = [
                    'class_teacher_id' => $class_teacher_id,
                    'school_id' => $school_id,
                    'sess_id' => $sess_id,
                    'term' => $term_id,
                    'sub_term' => 'full',
                    'grades' => $grades,
                    'result_settings' => $result_settings,
                ];
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
                        list($test, $total, $result_grade, $color, $grade_point) = $result->processResultInfo($student_result, $grades, $result_settings, $options);


                        //fetch the performance of students for each subject in this class
                        $subject_result_details = Result::where([
                            'subject_teacher_id' => $subject_teacher_id,
                            'school_id' => $school_id,
                            'sess_id' => $sess_id,
                            'term_id' => $term_id
                        ])->get();

                        list($subject_class_average, $subject_highest_score, $subject_lowest_score, $male_average, $female_average, $subject_totals) = $result->subjectStudentPerformance($subject_result_details, $grades, $result_settings, $options);

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
        $day = (int) date('d', strtotime($date));
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
        list($student_ids_arr, $students) = $teacher_obj->teacherstudentsInClass($attendance_class_id, $sess_id, $term_id, $school_id);

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



        return $this->render('reports.student_attendance_report', compact('attendances', 'class', 'percentage_present', 'percentage_absent', 'all_classes', 'no_of_days_in_month', 'date', 'attendance_class_id', 'student', 'marked_student_array', 'day', 'chart_only'));
    }

    public function analyseClassResult()
    {
        $request = request()->all();

        $school_id = $this->getSchool()->id;
        $sess_id = $this->getSession()->id;
        $term_id = $this->getTerm()->id;

        $grades = $this->getGrades();
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


        $options = [
            'class_teacher_id' => $class_teacher_id,
            'school_id' => $school_id,
            'sess_id' => $sess_id,
            'term' => $term_id,
            'sub_term' => 'full',
            'grades' => $grades,
            // 'result_settings' => $result_settings,
        ];

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
            $class_teacher = $subject_teacher->classTeacher;
            $curriculum_level_group_id = $class_teacher->level->levelGroup->id;

            $result_settings = $this->getResultSettings($curriculum_level_group_id);
            $result = new Result();

            list($subject_class_average, $subject_highest_score, $subject_lowest_score, $male_average, $female_average, $subject_totals) = $result->subjectStudentPerformance($results, $grades, $result_settings, $options);

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


        return $this->render('reports.academic_report_analysis', compact('subject_teachers', 'all_sessions', 'all_classes', 'term_array', 'class_teacher_id', 'sess_id', 'term_id', 'performance', 'performance_color', 'selected_session', 'selected_class', 'selected_term', 'chart_only', 'hide_selection', 'level_array'));

        //return $this->render('reports.academic_report_analysis', compact('subject_teachers', 'hide_selection', 'selected_session', 'selected_class', 'selected_term'));

    }

    public function subjectAverages()
    {
    }
}
