<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyClassroom extends Model
{
    //
    public function materials()
    {
        return $this->hasMany(DailyClassroomMaterial::class);
    }
    public function videos()
    {
        return $this->hasMany(DailyClassroomVideo::class);
    }

    public function posts()
    {
        return $this->hasMany(ClassroomPost::class);
    }

    public function attendees()
    {
        return $this->hasMany(DailyClassroomAttendee::class);
    }

    public function subjectTeacher()
    {
        return $this->belongsTo(SubjectTeacher::class);
    }
}
