<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupOfSchool extends Model
{
    //
    public function schools()
    {
        return $this->hasMany(School::class, 'group_of_school_id', 'id');
    }
}
