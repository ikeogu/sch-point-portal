<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModulePermission extends Model
{
    //

    public function module() {
        return $this->belongsTo(ActivatedModule::class);
    }

    
}
