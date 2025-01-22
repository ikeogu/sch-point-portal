<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassAttendance extends Model
{
    //
    protected $fillable = [
        'class_teacher_id',
        'student_ids'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function classTeacher()
    {
        return $this->belongsTo(ClassTeacher::class);
    }

    /**
     * Method to fetch students attendance report for chart view for a particular date and class
     *@param $sess_id: Academic Session for which the admission report is to be fetched
     *@param $options: array of extra conditions for the query
     *@return $levels: containing number of males and females in a particular level
     */
    function fetchAttendanceReport($school_id, $date, $teacher_id = null)
    {
        $class_teachers = ClassTeacher::where('school_id', $school_id)->get();
        if ($teacher_id) {
            $class_teachers = ClassTeacher::where(['school_id' => $school_id, 'teacher_id' => $teacher_id])->get();
        }

        if ($class_teachers != '[]') {
            foreach ($class_teachers as $class_teacher) {
                $attendance = ClassAttendance::where('class_teacher_id', $class_teacher->id)
                    ->whereDate('class_attendances.created_at', '=', $date)->first();

                $class_teacher->attendance_count = 0;
                if ($attendance) {
                    $student_ids = $attendance->student_ids;

                    $count = count(explode('~', $student_ids));

                    $class_teacher->attendance_count = $count;
                }

                $class_teacher->date = $date;
            }

            return $class_teachers;
        }
        return null;
    }

    /**
     *markedAttendance() method
     *@param array $options with 'option', 'fromDate', 'toDate' and 'id' as keys
     *@return $marked students
     */
    public function markedAttendance(array $options = [])
    {
        if ($options['option'] == 'class') {
            $day = (int) (date('d', strtotime($options['toDate'])));
            $attendance_month = date('Y-m', strtotime($options['toDate']));

            $marked_month_attendances =  ClassAttendance::where('class_teacher_id', $options['id'])
                ->where('student_ids', '!=', NULL)
                ->where('date', 'LIKE', '%' . $attendance_month . '%')
                //->select('id','student_ids','date')
                ->get();

            $marked_today =       ClassAttendance::where('class_teacher_id', $options['id'])
                ->where('student_ids', '!=', NULL)
                ->whereDate('date', '=', getDateFormat($options['toDate']))
                //->select('id','student_ids','date')
                ->first();

            $marked_student_array = [];
            $attendance_id = null;
            if ($marked_today) {

                //attendance taken
                $attendance_id = $marked_today->id;


                $date = $marked_today->date;

                $marked_student_array[$day] = explode('~', $marked_today->student_ids);
            }


            //this is needed to show all attendance for the month
            foreach ($marked_month_attendances as $marked_month_attendance) {
                if ($marked_month_attendance) {

                    $attendance_day = (int) (date('d', strtotime($marked_month_attendance->date)));
                    //attendance taken
                    $marked_student_array[$attendance_day] = explode('~', $marked_month_attendance->student_ids);
                }
            }
        } else if ($options['option'] == 'subject') {
            $day = (int) (date('d', strtotime($options['toDate'])));
            $attendance_month = date('Y-m', strtotime($options['toDate']));

            $marked_month_attendances =  SubjectAttendance::where('subject_teacher_id', $options['id'])
                ->where('student_ids', '!=', NULL)
                ->where('date', 'LIKE', '%' . $attendance_month . '%')
                //->select('id','student_ids','date')
                ->get();

            $marked_today =       SubjectAttendance::where('subject_teacher_id', $options['id'])
                ->where('student_ids', '!=', NULL)
                ->whereDate('date', '=', getDateFormat($options['toDate']))
                //->select('id','student_ids','date')
                ->first();

            $marked_student_array = [];
            $attendance_id = null;
            if ($marked_today) {

                //attendance taken
                $attendance_id = $marked_today->id;


                $date = $marked_today->date;

                $marked_student_array[$day] = explode('~', $marked_today->student_ids);
            }


            //this is needed to show all attendance for the month
            foreach ($marked_month_attendances as $marked_month_attendance) {
                if ($marked_month_attendance) {

                    $attendance_day = (int) (date('d', strtotime($marked_month_attendance->date)));
                    //attendance taken
                    $marked_student_array[$attendance_day] = explode('~', $marked_month_attendance->student_ids);
                }
            }
        }

        return array($marked_month_attendances, $marked_today, $marked_student_array, $attendance_id);
    }

    public function getClassAttendance($school_id, $class_teacher_id, $sess_id, $term_id)
    {
        $attendances = ClassAttendance::where([
            'school_id' => $school_id,
            'class_teacher_id' => $class_teacher_id,
            'sess_id' => $sess_id,
            'term_id' => $term_id
        ])->get();

        return $attendances;
    }
    public function studentClassAttendanceScore($student_id, $school_id, $class_teacher_id, $sess_id, $term_id)
    {
        $class_attendances = $this->getClassAttendance($school_id, $class_teacher_id, $sess_id, $term_id);
        $no_of_attendance_taken = $class_attendances->count();

        $no_of_times_present = 0;
        $attendance_score = 0;
        if ($no_of_attendance_taken > 0) {

            foreach ($class_attendances as $class_attendance) {
                $student_ids_array = explode('~', $class_attendance->student_ids);

                if (in_array($student_id, $student_ids_array)) {
                    $no_of_times_present++;
                }
            }

            $attendance_score = $no_of_times_present / $no_of_attendance_taken * 100;
        }

        return $attendance_score;
    }
}
