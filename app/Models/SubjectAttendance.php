<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubjectAttendance extends Model
{
    use SoftDeletes;
    //
    protected $fillable = [
        'subject_teacher_id',
        'student_ids'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function subjectTeacher()
    {
        return $this->belongsTo(SubjectTeacher::class);
    }

    /**
     * Method to fetch students attendance report for chart view for a particular date and class
     *@param $sess_id: Academic Session for which the admission report is to be fetched
     *@param $options: array of extra conditions for the query
     *@return $levels: containing number of males and females in a particular level
     */
    public function fetchAttendanceReport($school_id, $date, $teacher_id = null)
    {
        $subject_teachers = SubjectTeacher::where('school_id', $school_id)->get();
        if ($teacher_id) {
            $subject_teachers = SubjectTeacher::where(['school_id' => $school_id, 'teacher_id' => $teacher_id])->get();
        }

        if ($subject_teachers != '[]') {
            foreach ($subject_teachers as $subject_teacher) {
                $attendance = SubjectAttendance::where('subject_teacher_id', $subject_teacher->id)
                    ->whereDate('subject_attendances.created_at', '=', $date)->first();

                $subject_teacher->attendance_count = 0;
                if ($attendance) {

                    $student_ids = $attendance->student_ids;

                    $count = count(explode('~', $student_ids));

                    $subject_teacher->attendance_count = $count;
                }

                $subject_teacher->date = $date;
            }

            return $subject_teachers;
        }
        return null;
    }

    private function getSubjectAttendance($school_id, $subject_teacher_id, $sess_id, $term_id)
    {
        $attendances = SubjectAttendance::where([
            'school_id' => $school_id,
            'subject_teacher_id' => $subject_teacher_id,
            'sess_id' => $sess_id,
            'term_id' => $term_id
        ])->get();

        return $attendances;
    }
    public function studentSubjectAttendanceScore($student_id, $school_id, $subject_teacher_id, $sess_id, $term_id, $attendance_score_limit)
    {
        $subject_attendances = $this->getSubjectAttendance($school_id, $subject_teacher_id, $sess_id, $term_id);
        $no_of_attendance_taken = $subject_attendances->count();

        $no_of_times_present = 0;
        $attendance_score = 0;
        if ($no_of_attendance_taken > 0) {

            foreach ($subject_attendances as $subject_attendance) {
                $student_ids_array = explode('~', $subject_attendance->student_ids);

                if (in_array($student_id, $student_ids_array)) {
                    $no_of_times_present++;
                }
            }

            $attendance_score = convertPercentToUnitScore($attendance_score_limit, $no_of_times_present, $no_of_attendance_taken);
        }

        return $attendance_score;
    }
}
