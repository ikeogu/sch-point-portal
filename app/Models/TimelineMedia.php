<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class TimelineMedia extends Model
{
    protected $fillable = [ 'media_link', 'mime' ];

    protected $table = 'timeline_medias';

    public function getMediaLinkAttribute( $value )
    {
        return Storage::disk( "timeline" )->url( $value );
//        return Storage::url( $value );
    }
    //
}
