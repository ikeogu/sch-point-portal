<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtherFee extends Model
{
    //
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function school() {
        return $this->belongsTo(School::class);
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function level() {
        return $this->belongsTo(Level::class);
    }
    
    
}
