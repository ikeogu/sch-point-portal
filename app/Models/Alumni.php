<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alumni extends Model
{
    protected $table = 'alumni';
    //
    public function school()
    {
        return $this->belongsTo(School::class);
    }
    public function student()
    {
        return $this->belongsTo(Student::class);
    }
    public function graduateSession()
    {
        return $this->belongsTo(SSession::class, 'graduate_session', 'id');
    }
}
