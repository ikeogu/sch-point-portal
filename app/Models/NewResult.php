<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewResult extends Model
{
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

    public function processResultInfo($student_grade, $grades, $result_settings)
    {
        $test = $this->addCaScores($student_grade, $result_settings);

        $exam =  $student_grade->exam;


        $total = $this->addScores($test, $exam);

        list($result_grade, $color, $grade_point) = $this->resultGrade($total, $grades);


        return array($test, $total, $result_grade, $color, $grade_point);
    }

    public function addCaScores($result_detail, $result_settings)
    {
        $total_ca_score = 0;
        $no_of_ca = $result_settings->no_of_ca;
        if ($result_settings->display_exam_score_only_for_full_term === 'no') {
            for ($i = 1; $i <= $no_of_ca; $i++) {
                $assessment = 'ca' . $i;
                $score = $result_detail->$assessment;
                $total_ca_score += ($score) ? $score : 0;
            }
        }
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

    public function resultGrade($total, $grades)
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

    function emptyScore($test_score, $exam_score)
    {
        if ($test_score == "" || $exam_score == "") {
            return true;
        }
        return false;
    }

    private function checkTestAbsence($test_score)
    {
        if ($test_score == '' || $test_score == '-') {
            $test_score = 'No-Score';
        } else {
            $test_score = $test_score;
        }
        return $test_score;
    }

    private function  checkExamAbsence($exam_score)
    {
        if ($exam_score == '' || $exam_score == '-') {
            $exam_score = 'No-Score';
        } else {
            $exam_score = $exam_score;
        }
        return $exam_score;
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

    public function subjectStudentPerformance($result_details)
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
            if ($result_detail->total >= $subject_highest_score) {
                $subject_highest_score = $result_detail->total;
            }

            if (($result_detail->total != null) && $result_detail->total <= $subject_lowest_score) {
                $subject_lowest_score = $result_detail->total;
            }

            if (strtolower($result_detail->student->user->gender) == 'male') {
                $male_count++;

                $male_total_score += $result_detail->total;
            }

            if (strtolower($result_detail->student->user->gender) == 'female') {
                $female_count++;
                $female_total_score += $result_detail->total;
            }

            $total_score += $result_detail->total;
            $subject_totals[] = $result_detail->total;
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
            $result = Result::where([
                'sess_id' => $options['sess_id'],
                'school_id' => $options['school_id'],
                'term_id' => $term_id,
                'subject_teacher_id' => $subject_teacher_id
            ])->first();

            list($view_half, $view_full, $status_half, $status_full) = $this->resultStatusAction(($result) ? $result : null);

            $viewable = ['approved', 'published'];
            // if (($options['sub_term'] == 'half' && in_array($status_half, $viewable)) || ($options['sub_term'] == 'full' && in_array($status_full, $viewable))) {

            $student_result->result_action_array = array($view_half, $view_full, $status_half, $status_full);
            //$total_for_avg = $total_for_avg+$student_result->total;
            list($test, $total, $result_grade, $color, $grade_point) = $this->processResultInfo($student_result, $grades, $result_settings);


            //fetch the performance of students for each subject in this class
            $subject_result_details = Result::where([
                'subject_teacher_id' => $subject_teacher_id,
                'school_id' => $options['school_id'],
                'sess_id' => $options['sess_id'],
                'term_id' => $options['term']
            ])->get();

            list($subject_class_average, $subject_highest_score, $subject_lowest_score, $male_average, $female_average, $subject_totals) = $this->subjectStudentPerformance($subject_result_details);

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
        $result_settings = $options['result_settings'];


        $result_details = Result::with('subjectTeacher.subject')->where(
            [
                'class_teacher_id' => $class_teacher_id,
                'school_id' => $school_id,
                'sess_id' => $sess_id,
                'term_id' => $term_id,
                'student_id' => $student_id,
                'result_status' => 'Applicable'
            ]
        )->orderBy('subject_teacher_id')->get();

        $result_details_array = [];
        if ($result_details->isNotEmpty()) {

            $total_score = 0;

            $count = 0;

            foreach ($result_details as $student_result) :

                $sub_id = $student_result->subject_teacher_id;
                // check if result has been approved or not

                list($view_half, $view_full, $status_half, $status_full) = $this->resultStatusAction(($student_result) ? $student_result : null);
                $viewable = ['approved', 'published'];
                // if (($sub_term == 'half' && in_array($status_half, $viewable)) || ($sub_term == 'full' && in_array($status_full, $viewable))) {

                $subject_name = $student_result->subjectTeacher->subject->code;
                $total = $student_result->total;


                if ($sub_term == 'half') {
                    //we want to calculate the total ca for half term and convert to its equivalent 100%
                    // $total_ca = $student_result->ca1 + $student_result->ca2;
                    // $total = scoreInPercentage($total_ca, 30);
                    $total = $student_result->mid_term;
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
                $subjects[$sub_id] = $subject_name;

                $student_result->total = $total;
                $student_result->color = $color;
                $student_result->grade_point = $grade_point;

                $result_details_array[] = ['name' => $subject_name, 'grade' => $total];
            // }
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
        }
        $student->subjects = $subjects;
        $student->result_details_array = $result_details_array;



        return $student;
    }

    public function analyzeTeacherPerformance($teacher_id, $sess_id, $grades)
    {

        $subject_teacher_ids = Result::distinct()->where(['recorded_by' => $teacher_id, 'sess_id' => $sess_id])
            ->orderBy('subject_teacher_id')->pluck('subject_teacher_id');

        $subject_averages = [];
        $count_subjects = 0;
        $total_score = 0;
        if ($subject_teacher_ids != '[]') {
            foreach ($subject_teacher_ids as $subject_teacher_id) {
                $subject_teacher = SubjectTeacher::with('subject')->findOrFail($subject_teacher_id);
                $average = Result::where(['recorded_by' => $teacher_id, 'sess_id' => $sess_id, 'subject_teacher_id' => $subject_teacher_id])->where('exam', '!=', null)->avg('total');

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

        $results = Result::groupBy('subject_teacher_id')->with('subjectTeacher.subject', 'classTeacher.c_class')->where(['recorded_by' => $teacher_id, 'sess_id' => $sess_id, 'school_id' => $school_id, 'term_id' => $term_id])->where('exam', '!=', null)->orderBy('sess_id')->select('*', \DB::raw('AVG(total) as average'))->get();

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
                ->select(\DB::raw('AVG(total) as average'))->first();
            if ($result_average) {
                $result_details[] = ['average' => $result_average->average];
            } else {
                $result_details[] = ['average' => 0.00];
            }
        }
        return $result_details;
    }
}
