<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ResultAction extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'school_id',
        'sess_id',
        'subject_teacher_id',
        'actions_term_1',
        'actions_term_2',
        'actions_term_3'
    ];
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
    public function sess()
    {
        return $this->belongsTo(SSession::class, 'sess_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function performResultAction($school_id, $subject_teacher_id, $sess_id)
    {
        $action_result = ResultAction::where(['sess_id' => $sess_id, 'subject_teacher_id' => $subject_teacher_id, 'school_id' => $school_id])->first();
        if (!$action_result) {
            $action_result = new ResultAction();
            $action_result->school_id = $school_id;
            $action_result->subject_teacher_id = $subject_teacher_id;
            $action_result->sess_id = $sess_id;
            $action_result->save();
        }

        return $action_result;
    }
}
