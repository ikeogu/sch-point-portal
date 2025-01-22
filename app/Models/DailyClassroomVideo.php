<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyClassroomVideo extends Model
{
    //
    public function youtubeVideo()
    {
        return $this->belongsTo(YoutubeVideo::class);
    }
}
