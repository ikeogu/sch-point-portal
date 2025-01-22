<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentsOfferingSubject extends Model
{
    use SoftDeletes;
    //
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function subjectTeacher()
    {
        return $this->belongsTo(SubjectTeacher::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function session()
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

    public function addStudentToSubjectClass($student_id, $subject_teacher_id, $sess_id, $term_id, $school_id)
    {
        $student_in_class = StudentsOfferingSubject::where([
            'student_id' => $student_id,
            'subject_teacher_id' => $subject_teacher_id,
            'sess_id' => $sess_id,
            // 'term_id' => $term_id,
            'school_id' => $school_id
        ])->first();
        if (!$student_in_class) {
            $student_in_class = new StudentsOfferingSubject();
        }
        $student_in_class->subject_teacher_id = $subject_teacher_id;
        $student_in_class->sess_id = $sess_id;
        $student_in_class->term_id = $term_id;
        $student_in_class->school_id = $school_id;
        $student_in_class->student_id = $student_id;
        $student_in_class->save();

        $student_result = Result::where([
            'subject_teacher_id' => $subject_teacher_id,
            'school_id' => $school_id,
            'sess_id' => $sess_id,
            'term_id' => $term_id,
            'student_id' => $student_id,
        ])->first();
        if ($student_result) {
            $student_result->result_status = 'Applicable';
            $student_result->save();
        }
        return true;
    }

    public function removeStudentFromSubjectClass($student_id, $subject_teacher_id, $sess_id, $term_id, $school_id)
    {
        $student_in_class = StudentsOfferingSubject::where([
            'student_id' => $student_id,
            'subject_teacher_id' => $subject_teacher_id,
            'sess_id' => $sess_id,
            // 'term_id' => $term_id,
            'school_id' => $school_id
        ])->first();
        if ($student_in_class) {
            $student_in_class->delete();
            $student_result = Result::where([
                'subject_teacher_id' => $subject_teacher_id,
                'school_id' => $school_id,
                'sess_id' => $sess_id,
                'term_id' => $term_id,
                'student_id' => $student_id,
            ])->first();
            if ($student_result) {
                $student_result->result_status = 'Not Applicable';
                $student_result->save();
            }
        }
    }
}
