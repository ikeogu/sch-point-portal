<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackageModule extends Model
{
    use HasFactory;
    public function package()
    {
        return $this->belongsTo(Package::class);
    }
    public function module()
    {
        return $this->belongsTo(AvailableModule::class, 'module_id', 'id');
    }
}
