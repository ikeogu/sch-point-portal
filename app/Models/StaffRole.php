<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffRole extends Model
{
    //
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function staff() 
    {
        return $this->belongsTo(Staff::class);
    }

    public function addStaffRole($staff_id, $school_id, $role)
    {
    	$staff_role = StaffRole::where(['school_id'=>$school_id, 'staff_id'=>$staff_id, 'role'=>$role])->first();

        if (!$staff_role) {
            $staff_role = new StaffRole();

            $staff_role->staff_id = $staff_id;
            $staff_role->school_id = $school_id;
            $staff_role->role = strtolower($role);
            $staff_role->save();

            if($staff_role->save()){
                return $this->id;
            }
        }

        
        return false;
    }
}
