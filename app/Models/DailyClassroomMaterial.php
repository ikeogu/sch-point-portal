<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyClassroomMaterial extends Model
{
    //
    public function classroom()
    {
        return $this->belongsTo(DailyClassroom::class);
    }
}
