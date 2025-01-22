<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegistrationPin extends Model
{
    //
    /**
     * Get the school that owns the RegistrationPin
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function school()
    {
        return $this->belongsTo(School::class);
    }
}
