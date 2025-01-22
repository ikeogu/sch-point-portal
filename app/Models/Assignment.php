<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\SoftDeletes;

class Assignment extends Model
{
    use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'school_id',
        'subject_teacher_id',
        'deadline',
        'assignment_details',
        'sess_id',
        'term_id',
        'download_link',
        'is_marked'
    ];

    protected $dates = ['deadline'];

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
    public function session()
    {
        return $this->belongsTo(SSession::class, 'sess_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     *The return value is in array form and a foreach loop should be used to fetch each element
     */
    public function studentAssignments()
    {
        return $this->hasMany(AssignmentStudent::class);
    }

    public function assignmentMedias()
    {
        return $this->hasMany(AssignmentMedia::class);
    }

    /**
     *teacherAssignments() method
     *@param $teacher_id gotten from $staff_id of Staff table
     *@return $details
     */
    public function teacherAssignmentsDetails($assignments)
    {

        foreach ($assignments as $assignment) :

            $detail = SubjectTeacher::find($assignment->subject_teacher_id);
            if ($detail) {
                $subject_id = $detail->subject_id;
                $class_id = $detail->classTeacher->class_id;
                $staff = Staff::find($detail->teacher_id);


                $subject = Subject::find($subject_id);
                $class = CClass::find($class_id);
                $level = Level::find($class->level);

                $assignment->teacher = $staff->user;
                $assignment->subject = $subject;
                $assignment->class = $class;
                $assignment->level = $level;
            }

        endforeach;
        return $assignments;
    }
}
