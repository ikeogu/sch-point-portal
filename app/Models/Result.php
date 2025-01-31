<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Result extends Model
{
    use SoftDeletes;

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sess()
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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function classTeacher()
    {
        return $this->belongsTo(ClassTeacher::class);
    }

    public function subjectTeacher()
    {
        return $this->belongsTo(SubjectTeacher::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Staff::class, 'recorded_by', 'id');
    }

    //
    public function studentResult($sess_id, $student_id, $reg_no, $school_id, $term_id, $subject_teacher_id, $class_teacher_id, $teacher_id)
    {
        $result = Result::where(
            [
                'sess_id' => $sess_id,
                'student_id' => $student_id,
                'school_id' => $school_id,
                'class_teacher_id' => $class_teacher_id,
                'term_id' => $term_id,
                'subject_teacher_id' => $subject_teacher_id
            ]
        )->first();


        if (!$result) {
            $result = new Result();
            $result->school_id = $school_id;
            $result->student_id = $student_id;
            $result->reg_no = $reg_no;
            $result->sess_id = $sess_id;
            $result->class_teacher_id = $class_teacher_id;
            $result->recorded_by = $teacher_id;
            $result->term_id = $term_id;
            $result->subject_teacher_id = $subject_teacher_id;
            //other fields are nullable
            $result->save();
        }




        return $result;
    }

    public function processResultInfo($student_grade, $grades, $result_settings, $options)
    {
        $sub_term = $options['sub_term'];
        $test = $this->addCaScores($student_grade, $result_settings);

        $exam =  $student_grade->exam;

        if ($sub_term == 'half') {
            $total = $student_grade->mid_term1 + $student_grade->mid_term2;
        } else {
            $total = $this->addScores($test, $exam);
        }


        list($result_grade, $color, $grade_point) = $this->resultGrade($total, $grades);


        return array($test, $total, $result_grade, $color, $grade_point);
    }



    public function updateResultDetails($result_detail, $result_settings)
    {
        $sub_attendance_obj = new SubjectAttendance();
        $attendance_score_limit = $result_settings->attendance_score_limit;

        $student_id = $result_detail->student_id;
        $school_id = $result_detail->school_id;
        $subject_teacher_id = $result_detail->subject_teacher_id;
        $sess_id = $result_detail->sess_id;
        $term_id = $result_detail->term_id;

        $result_detail->attendance_score = $sub_attendance_obj->studentSubjectAttendanceScore($student_id, $school_id, $subject_teacher_id, $sess_id, $term_id, $attendance_score_limit);


        $result_detail->test = $this->addCaScores($result_detail, $result_settings);

        $result_detail->mid_term = $result_detail->mid_term1 + $result_detail->mid_term2;
        $result_detail->midterm_to_ca = convertPercentToUnitScore($result_detail->mid_term, $result_settings->midterm_score_limit);

        return $result_detail;
    }

    public function addCaScores($result_detail, $result_settings)
    {
        $sub_attendance_obj = new SubjectAttendance();
        $attendance_score_limit = $result_settings->attendance_score_limit;

        $student_id = $result_detail->student_id;
        $school_id = $result_detail->school_id;
        $subject_teacher_id = $result_detail->subject_teacher_id;
        $sess_id = $result_detail->sess_id;
        $term_id = $result_detail->term_id;

        $total_ca_score = 0;
        $no_of_ca = $result_settings->no_of_ca;
        $display_exam_score_only_for_full_term = $result_settings->display_exam_score_only_for_full_term;
        $mid_term = 0;
        $attendance_score = 0;
        // if ($result_settings->display_exam_score_only_for_full_term === 'no') {
        // we want to make sure that previous inputed results before the major upgrade
        // remain the same so that there would be no descrepancies.
        // The major upgrade will affect result that will be with date from year 2022 and upwards

        $result_date = (int) date('Y', strtotime($result_detail->updated_at));
        if ($result_date < 2022) {

            $ca1 = $result_detail->ca1;
            $ca2 = $result_detail->ca2;
            $ca3 = $result_detail->ca3;
            if ($result_settings->add_midterm_score_to_full_result == 'yes') {
                $mid_term = $result_detail->mid_term1 / 10;
                $total_ca_score = $this->addScores($mid_term, $ca1, $ca2, $ca3);
            } else {
                $total_ca_score = $this->addScores($ca1, $ca2, $ca3);
            }
        } else {

            if ($result_settings->add_midterm_score_to_full_result == 'yes') {

                $mid_term_score = $result_detail->mid_term1 + $result_detail->mid_term2;
                $midterm_score_limit = $result_settings->midterm_score_limit;
                $mid_term = convertPercentToUnitScore($mid_term_score, $midterm_score_limit);
            }

            if ($result_settings->add_attendance_to_ca == 'yes') {

                $attendance_score = $sub_attendance_obj->studentSubjectAttendanceScore($student_id, $school_id, $subject_teacher_id, $sess_id, $term_id, $attendance_score_limit);
            }

            // we want to make sure ca tests are calculated along exam only when $display_exam_score_only_for_full_term is 'no'
            if ($display_exam_score_only_for_full_term  === 'no') {

                for ($i = 1; $i <= $no_of_ca; $i++) {
                    $assessment = 'ca' . $i;
                    $score = $result_detail->$assessment;
                    $total_ca_score += ($score) ? $score : 0;
                }
                $total_ca_score += $mid_term;
                $total_ca_score += $attendance_score;
            }
        }
        // }
        return $total_ca_score;
    }
    public function addScores($score1, $score2, $score3 = null, $score4 = null, $score5 = null)
    {
        if ($score1 == null && $score2 == null && $score3 == null && $score4 == null && $score5 == null) {
            $total = null;
            return $total;
        }
        if ($score1 == null) {
            $score1 = 0;
        }
        if ($score2 == null) {
            $score2 = 0;
        }
        if ($score3 == null) {
            $score3 = 0;
        }
        if ($score4 == null) {
            $score4 = 0;
        }
        if ($score5 == null) {
            $score5 = 0;
        }
        $total = $score1 + $score2 + $score3 + $score4 + $score5;
        return $total;
    }

    public function resultRemark($student_grade, $grades)
    {
        $remark = "Undefined";
        if (!empty($grades)) {
            foreach ($grades as $grade_detail) {

                if ($student_grade == $grade_detail->grade) {

                    $remark = $grade_detail->interpretation;
                }
            }
        }
        return $remark;
    }

    public function resultGrade($total, $grades = [])
    {
        $grade = 'F';
        $color = '#F00000';
        $grade_point = 0;
        if (!empty($grades)) {
            foreach ($grades as $grade_detail) {
                //grade range (example 80-100)
                $higher_point = $grade_detail->upper_limit;
                $lower_point = $grade_detail->lower_limit;
                //cast the grade_range to array
                /*$grades_range_array = explode('-', $grade_range);
				$lower_point =  $grades_range_array[0];
				$higher_point = $grades_range_array[1];*/
                if ($higher_point < 100) {
                    //To round a number down e.g 85.99 = 85.0
                    $higher_point = floor($higher_point) + 0.99;
                }

                if ($total == null) {
                    $grade = null;
                    $color = null;
                    $grade_point = null;
                } elseif ($total <= $higher_point and $total >= $lower_point) {
                    $grade = $grade_detail->grade;
                    $color = $grade_detail->color_code;
                    $grade_point = sprintf("%01.2f", $grade_detail->grade_point);

                    break;
                }
            }
        }


        return array($grade, $color, $grade_point);
    }





    public function resultStatus($grade, $test_score, $exam_score)
    {
        if ($grade != 'F' /*AND $grade != 'E'*/) {
            $status = "Pass";
        } else {
            $status = "Fail (Repeat)";
        }
        return $status;
    }

    public function resultOverflow($test_score, $exam_score, $ca_score_limit)
    {
        if (
            $test_score > $ca_score_limit || $exam_score > (100 - $ca_score_limit)
        ) {
            return true;
        }
        return false;
    }

    //This ensures that once a result is submitted or approved no more editing is made
    public function getAllResultActions($result)
    {
        $edit_midterm = $edit_exam = false;
        if ($result != null) {
            $full_status = $result->fullterm_status;
            $half_status = $result->midterm_status;
            if ($half_status === 'Not Submitted' || $half_status == 'rejected') {
                $edit_midterm = true;
            }
            if ($full_status == 'Not Submitted' || $full_status == 'rejected') {
                $edit_exam = true;
            }
        }
        return array($edit_midterm, $edit_exam);
    }

    //This ensures that once a result is submitted or approved no more editing is made
    public function resultStatusAction($result)
    {
        $view_half = $view_full =  'false';
        $status_half = $status_full = 'Not Submitted';
        if ($result != null) {
            $full = $result->fullterm_status;
            $half = $result->midterm_status;
            if ($half == 'submitted' || $half == 'approved' || $half == 'published') {
                $view_half = 'true';
                $status_half = $half;
            }
            if ($full == 'submitted' || $full == 'approved' || $full == 'published') {
                $view_full = 'true';
                $status_full = $full;
            }
            if ($half == 'rejected') {
                $status_half = $half;
            }
            if ($full == 'rejected') {
                $status_full = $full;
            }
        }
        return array($view_half, $view_full, $status_half, $status_full);
    }

    public function subjectStudentPerformance(mixed $result_details, mixed $grades = [], mixed $result_settings = null, $options = [])
    {
        $subject_class_average = 0;
        $male_average = 0;
        $female_average = 0;
        $subject_highest_score = 0;
        $subject_lowest_score = 100;

        $male_total_score = 0;
        $female_total_score = 0;
        $total_score = 0;
        $male_count = 0;
        $female_count = 0;
        $count = 0;
        $subject_totals = [];
        foreach ($result_details as $result_detail) {
            $count++;
            list($test, $total, $result_grade, $color, $grade_point) = $this->processResultInfo($result_detail, $grades, $result_settings, $options);
            if ($total >= $subject_highest_score) {
                $subject_highest_score = $total;
            }

            if (($total != null) && $total <= $subject_lowest_score) {
                $subject_lowest_score = $total;
            }

            if (strtolower($result_detail->student->user->gender) == 'male') {
                $male_count++;

                $male_total_score += $total;
            }

            if (strtolower($result_detail->student->user->gender) == 'female') {
                $female_count++;
                $female_total_score += $total;
            }

            $total_score += $total;
            $subject_totals[] = $total;
        }
        if ($male_count > 0) {
            $male_average = $male_total_score / $male_count;
        }

        if ($female_count > 0) {
            $female_average = $female_total_score / $female_count;
        }
        if ($count > 0) {
            $subject_class_average = sprintf("%01.1f", $total_score / $count);
        }


        return array($subject_class_average, $subject_highest_score, $subject_lowest_score, $male_average, $female_average, $subject_totals);
    }

    public function processStudentResults($student_results, $options)
    {
        $class_result_averages = [];
        $total_subject_class_average = 0;
        $total_student_score = 0;
        $result_count = 0;
        foreach ($student_results as $student_result) :

            $subject_teacher_id = $student_result->subject_teacher_id;
            //$student_result->user = Student::find($student_result->student_id)->user;
            //$subject_teacher = SubjectTeacher::find($subject_teacher_id);

            //$student_result->teacher = User::find($student_result->teacher->user_id);

            //$student_result->subject = $subject_teacher->subject;

            $term_id = $options['term'];
            $grades = $options['grades'];
            $result_settings = $options['result_settings'];
            // $result = Result::where([
            //     'sess_id' => $options['sess_id'],
            //     'school_id' => $options['school_id'],
            //     'term_id' => $term_id,
            //     'subject_teacher_id' => $subject_teacher_id
            // ])->first();

            // list($view_half, $view_full, $status_half, $status_full) = $this->resultStatusAction(($result) ? $result : null);

            // $viewable = ['approved', 'published'];
            // if (($options['sub_term'] == 'half' && in_array($status_half, $viewable)) || ($options['sub_term'] == 'full' && in_array($status_full, $viewable))) {

            // $student_result->result_action_array = array($view_half, $view_full, $status_half, $status_full);
            //$total_for_avg = $total_for_avg+$student_result->total;
            list($test, $total, $result_grade, $color, $grade_point) = $this->processResultInfo($student_result, $grades, $result_settings, $options);


            //fetch the performance of students for each subject in this class
            $subject_result_details = Result::where([
                'subject_teacher_id' => $subject_teacher_id,
                'school_id' => $options['school_id'],
                'sess_id' => $options['sess_id'],
                'term_id' => $options['term'],
                'result_status' => 'Applicable'
            ])->get();

            list($subject_class_average, $subject_highest_score, $subject_lowest_score, $male_average, $female_average, $subject_totals) = $this->subjectStudentPerformance($subject_result_details, $grades, $result_settings, $options);

            $student_result = $this->updateResultDetails($student_result, $result_settings);

            $student_result->test = $test;
            $student_result->total = $total;
            $student_result->result_grade = $result_grade;
            $student_result->color = $color;
            $student_result->grade_point = $grade_point;
            $student_result->remark = $this->resultRemark($result_grade, $grades);
            $student_result->subject_class_average = $subject_class_average;
            $student_result->subject_highest_score = $subject_highest_score;
            $student_result->subject_lowest_score = $subject_lowest_score;
            $student_result->male_average = $male_average;
            $student_result->female_average = $female_average;
            $student_result->subject_totals = $subject_totals;
            if ($total != null) {
                $result_count++;
                $total_subject_class_average += $subject_class_average;
                $total_student_score += $total;
            }
            $student_result->position = rankResult($total, $subject_totals);
        // }
        endforeach;
        return array($student_results, $total_subject_class_average, $total_student_score, $result_count);
    }
    public function fetchClassStudentResultAverage($options)
    {
        $class_teacher_id = $options['class_teacher_id'];
        $school_id = $options['school_id'];
        $sess_id = $options['sess_id'];
        $term_id = $options['term'];
        $student_result_average = Result::groupBy('student_id')->where(
            [
                'class_teacher_id' => $class_teacher_id,
                'school_id' => $school_id,
                'sess_id' => $sess_id,
                'term_id' => $term_id,
                'result_status' => 'Applicable'
            ]
        )->whereRaw('total IS NOT NULL')->select('student_id', DB::raw('AVG(total) as average'))->get();
        return $student_result_average;
    }

    public function analyseStudentsResult($student, $options)
    {
        $grades = $options['grades']; // $this->getGrades();//Grade::where('school_id', $school_id)->get();//Grade::all();

        $subjects = [];
        $student_id = $student->id;
        $class_teacher_id = $options['class_teacher_id'];
        $school_id = $options['school_id'];
        $sess_id = $options['sess_id'];
        $term_id = $options['term'];
        $sub_term = $options['sub_term'];
        $approval_status = 'fullterm_status';
        if ($sub_term === 'half') {
            $approval_status = 'midterm_status';
        }
        $result_settings = $options['result_settings'];

        $class_subjects = SubjectTeacher::with('subject')->where([
            'class_teacher_id' => $class_teacher_id,
            'school_id' => $school_id
        ])/*->where('teacher_id', '!=', NULL)*/->orderBy('id')->get();

        $result_details_array = [];
        if ($class_subjects->isNotEmpty()) {

            $total_score = 0;

            $count = 0;
            $result_details = [];
            foreach ($class_subjects as $subject_teacher) :

                $total = null;
                $color = '';
                $grade_point = null;
                $count_subject_result = Result::where(
                    [
                        'subject_teacher_id' => $subject_teacher->id,
                        'school_id' => $school_id,
                        'sess_id' => $sess_id,
                        'term_id' => $term_id,
                        'result_status' => 'Applicable'
                    ]
                )->where($approval_status, '!=', 'Not Submitted')->count();
                if ($count_subject_result > 0) {
                    $student_result = Result::where(
                        [
                            'subject_teacher_id' => $subject_teacher->id,
                            'school_id' => $school_id,
                            'sess_id' => $sess_id,
                            'term_id' => $term_id,
                            'student_id' => $student_id,
                            'result_status' => 'Applicable'
                        ]
                    )->first();

                    $sub_id = $subject_teacher->id;
                    $subject_name = $subject_teacher->subject->code;

                    $subjects[$sub_id] = $subject_name;
                    if ($student_result) {
                        # code...

                        // check if result has been approved or not

                        // list($view_half, $view_full, $status_half, $status_full) = $this->resultStatusAction(($student_result) ? $student_result : null);
                        // $viewable = ['approved', 'published'];
                        // if (($sub_term == 'half' && in_array($status_half, $viewable)) || ($sub_term == 'full' && in_array($status_full, $viewable))) {


                        if ($sub_term == 'half') {
                            $total = $student_result->mid_term1 + $student_result->mid_term2;
                        } else {


                            $test = $this->addCaScores($student_result, $result_settings);
                            $exam =  $student_result->exam;
                            $total = $this->addScores($test, $exam);
                        }


                        list($grade, $color, $grade_point) = $this->resultGrade($total, $grades);

                        // if ($sub_term == 'half') {
                        //     //return to the normal value
                        //     $total_ca = $student_result->ca1 + $student_result->ca2;
                        //     $total = $total_ca;
                        // }
                        if ($total != null) {
                            $count++;
                            $total_score += $total;
                        }

                        $student_result = $this->updateResultDetails($student_result, $result_settings);
                        $student_result->total = $total;
                        $student_result->color = $color;
                        $student_result->grade_point = $grade_point;
                        $result_details[] = $student_result;
                    }

                    $result_details_array[] = ['name' => $subject_name, 'color' => $color, 'grade' => $total, 'total' => $total, 'grade_point' => $grade_point];
                }

            endforeach;

            $student->result_details = $result_details;
            //average of all subjects offered by this particular student
            if ($count == 0) {
                $average = 0;
            } else {
                $average = $total_score / $count;
            }
            // if ($sub_term == 'half') {
            //     //we want to calculate the average for half term and convert to its equivalent 100%
            //     $average_half_term = $average;

            //     $average = scoreInPercentage($average_half_term, 30);
            // }


            list($grade, $color, $grade_point) = $this->resultGrade($average, $grades);

            // if ($sub_term == 'half') {
            //     //return to the normal value
            //     $average = $average_half_term;
            // }

            $average = sprintf("%01.1f", $average);
            $student->average = ($average) ? $average : 0.00;
            $student->gender = $student->user->gender;
            $student->average_color = $color;
            $student->average_grade = $grade;
            $student->grade_point = $grade_point;

            $class_attendance_obj = new ClassAttendance();
            $class_attendance_score = $class_attendance_obj->studentClassAttendanceScore($student_id, $school_id, $class_teacher_id, $sess_id, $term_id);

            $student->class_attendance = sprintf("%01.1f", $class_attendance_score);
        }
        $student->subjects = $subjects;
        $student->result_details_array = $result_details_array;



        return $student;
    }

    public function analyzeTeacherPerformance($teacher_id, $sess_id, $grades)
    {

        $subject_teacher_ids = Result::distinct()->where(['recorded_by' => $teacher_id, 'sess_id' => $sess_id, 'result_status' => 'Applicable'])
            ->orderBy('subject_teacher_id')->pluck('subject_teacher_id');

        $subject_averages = [];
        $count_subjects = 0;
        $total_score = 0;
        if ($subject_teacher_ids != '[]') {
            foreach ($subject_teacher_ids as $subject_teacher_id) {
                $subject_teacher = SubjectTeacher::with('subject')->findOrFail($subject_teacher_id);
                $average = Result::where(['recorded_by' => $teacher_id, 'sess_id' => $sess_id, 'subject_teacher_id' => $subject_teacher_id, 'result_status' => 'Applicable'])->where('exam', '!=', null)->avg('total');

                list($grade, $color, $grade_point) = $this->resultGrade($average, $grades);
                $subject_teacher->subject_average =  (float) sprintf("%01.1f", $average);
                $subject_teacher->color =  $color;
                $subject_teacher->grade_point =  $grade_point;
                $subject_averages[] = $subject_teacher;

                if ($average > 0) {
                    $count_subjects++;
                    $total_score += $average;
                }
            }
        }
        if ($count_subjects < 1) {
            $performance_average = 0;
        } else {
            $performance_average = (float) sprintf("%01.1f", $total_score / $count_subjects);
        }



        return array($subject_averages, $performance_average);
    }

    public function analyzeTeacherTermlySubjectPerformance($teacher_id, $school_id, $sess_id, $term_id)
    {

       // $results = Result::groupBy('subject_teacher_id')->with('subjectTeacher.subject', 'classTeacher.c_class')->where(['recorded_by' => $teacher_id, 'sess_id' => $sess_id, 'school_id' => $school_id, 'term_id' => $term_id, 'result_status' => 'Applicable'])->where('exam', '!=', null)->orderBy('sess_id')->select('*', DB::raw('AVG(total) as average'))->get();
	$results = Result::select(
            'subject_teacher_id',
            DB::raw('AVG(total) as average') // Aggregate function for the average total
        )
        ->with(['subjectTeacher.subject', 'classTeacher.c_class']) // Eager loading relationships
        ->where([
            'recorded_by' => $teacher_id,
            'sess_id' => $sess_id,
            'school_id' => $school_id,
            'term_id' => $term_id,
            'result_status' => 'Applicable',
        ])
        ->whereNotNull('exam') // Check for non-null exam column
        ->groupBy('subject_teacher_id', 'sess_id') // Group by the necessary column
        ->orderBy('sess_id') // Ordering
        ->get();
        return $results;
    }
    public function studentCummulativePerformance($student_id)
    {
        $terms = [1, 2, 3];
        $result_details = [];
        foreach ($terms as $term) {

            $result_average = Result::groupBy('term_id')
                ->where('student_id', $student_id)
                ->where('exam', '!=', NULL)
                ->where('term_id', $term)
                ->select(DB::raw('AVG(total) as average'))->first();
            if ($result_average) {
                $result_details[] = ['average' => $result_average->average];
            } else {
                $result_details[] = ['average' => 0.00];
            }
        }
        return $result_details;
    }
}