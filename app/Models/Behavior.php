<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Behavior extends Model
{
    //
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

    public function rateStudent($school_id, $sess_id, $term_id, $value, $student_id, $field)
    {
        $behavior = Behavior::where(['school_id' => $school_id, 'student_id' => $student_id, 'sess_id' => $sess_id, 'term_id' => $term_id])->first();

        if (!$behavior) {
            $behavior = new Behavior();
        }
        $behavior->school_id = $school_id;
        $behavior->student_id = $student_id;
        $behavior->sess_id = $sess_id;
        $behavior->term_id = $term_id;
        $behavior->reg_no = Student::find($student_id)->registration_no;
        $behavior->$field = $value;

        $behavior->save();
    }
}
