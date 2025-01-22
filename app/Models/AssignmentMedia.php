<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class AssignmentMedia extends Model
{
    //
    protected $fillable = ['media_link', 'mime'];

    protected $table = 'assignment_medias';

    public function getMediaLinkAttribute($value)
    {
        return Storage::disk("public")->url($value);
        //        return Storage::url( $value );
    }
    //
}
