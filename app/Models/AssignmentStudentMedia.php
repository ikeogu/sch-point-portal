<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class AssignmentStudentMedia extends Model
{
    //
    protected $fillable = ['assignment_student_id', 'media_link', 'mime'];

    protected $table = 'assignment_student_medias';

    public function getMediaLinkAttribute($value)
    {
        return Storage::disk("public")->url($value);
        //        return Storage::url( $value );
    }
    //
}
