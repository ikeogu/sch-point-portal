<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssignmentStudent extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'school_id',
        'student_id',
        'assignment_id',
        'answer_link',
        'sess_id',
        'term_id',
        'student_answer'

    ];
    protected $dates = ['date'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function assignment()
    {
        return $this->belongsTo(Assignment::class);
    }

    public function assignmentStudentMedias()
    {
        return $this->hasMany(AssignmentStudentMedia::class);
    }

    public function submitAssignment(array $options = [], $action = 'insert')
    {

        if ($action == 'update') {
            $assignment_student = AssignmentStudent::find($options['id']);
            $assignment_student->update($options);
            return  $assignment_student;
        }
        $assignment_student = AssignmentStudent::create($options);
        return  $assignment_student;
    }
}
