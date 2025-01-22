<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditTrail extends Model
{
    //
    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id', 'id');
    }

    public function addEvent($request)
    {


        $id = $request->actor_id; //this is the id of the user in users table
        $name = $request->actor_name;
        $role = $request->actor_role;
        $school_id = $request->school_id;
        $actor_action = $request->action;


        $audit_trail = $this; //new AuditTrail();

        $audit_trail->school_id = $school_id;
        $audit_trail->actor_id = $id;
        $audit_trail->role = $role;
        $audit_trail->name = $name;
        $audit_trail->action_details = $actor_action;
        $audit_trail->save();
    }
}
