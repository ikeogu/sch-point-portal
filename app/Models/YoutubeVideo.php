<?php

namespace App\Models;

use Youtube;
use Illuminate\Database\Eloquent\Model;

class YoutubeVideo extends Model
{
    //
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function dailyClassrooms() {
        return $this->hasMany(DailyClassroomVideo::class);
    }


    public function storeVideo($request)
    {
        $video = Youtube::upload($request->file('video')->getPathName(), [
            'title'       => $request->input('title'),
            'description' => $request->input('description')
        ], 'unlisted');

        $youtube = new  YoutubeVideo();
        $youtube->school_id = $request->school_id;
        $youtube->video_title = $request->title;
        $youtube->description = $request->description;
        $youtube->youtube_id = $video->getVideoId();
        $youtube->save();

        return $youtube;
    }

    public function deleteVideo($videoId)
    {
        Youtube::delete($videoId);
    }
}


