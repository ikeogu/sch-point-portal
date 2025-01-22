<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Timeline extends Model
{
    protected $fillable = ['school_id', 'user_id', 'content', 'visible_to', 'status'];

    public function timelineMedias()
    {
        return $this->hasMany(TimelineMedia::class);
    }
    public function comments()
    {
        return $this->hasMany(TimelineComment::class);
    }

    // public function student()
    // {
    //     return $this->belongsTo(Student::class, 'user_id', 'user_id');
    // }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function addTimelineMedia($medias)
    {
        $media_link = $medias["file_link"];
        $mime = $medias["type"];
        $this->timelineMedias()->create(compact('media_link', 'mime'));
    }

    //    public function addTimelineMedia( $media_link )
    //    {
    //        $this->timelineMedias()->create( compact('media_link'));
    //    }

    public function scopeOnDays($query, $filters)
    {
        if ($day = $filters['day'])
            $query->whereDay('created_at', $day);

        if ($month = $filters['month'])
            $query->whereMonth('created_at', $month);

        if ($year = $filters['year'])
            $query->whereYear('created_at', $year);
    }

    //    public function scopeTimeline( $query, $filters )
    //    {
    //        if ($day = $filters['day'])
    //            $query->whereDay( 'created_at', Carbon::parse( $day )->day );
    //
    //        if ($month = $filters['month'])
    //            $query->whereMonth( 'created_at', Carbon::parse( $month )->month );
    //
    //        if ($year = $filters['year'])
    //            $query->whereYear( 'created_at', year );
    //    }

    //
}
