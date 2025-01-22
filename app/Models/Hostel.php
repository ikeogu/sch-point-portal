<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hostel extends Model
{
    
    public function school() {
        return $this->belongsTo(School::class);
    }

}