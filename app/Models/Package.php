<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;
    public function packageModules()
    {
        return $this->hasMany(PackageModule::class);
    }
    public function schools()
    {
        return $this->hasMany(School::class);
    }
}
