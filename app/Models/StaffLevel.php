<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffLevel extends Model
{
    //
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function school() {
        return $this->belongsTo(School::class);
    }
    
    
}
