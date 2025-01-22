<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    //
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'school_id',
        'grade',
        'interpretation',
        'grade_range',
        'grade_point',
        'color_code'
    ];

    public function school() {
        return $this->belongsTo(School::class);
    }
    public function curriculumLevelGroup() {
        return $this->belongsTo(CurriculumLevelGroup::class);
    }
}
