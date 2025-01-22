<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubjectActivity extends Model
{
    //

    public function addEvent($request)
    {
    	$today = todayDate();
		$time = date('H:i:s', strtotime('now'));
		
		$name = $request->actor_name;
		$school_id = $request->school_id;
		$subject_teacher_id = $request->subject_teacher_id;
		$actor_action = $request->action;
		
		$action = json_encode( array (
					'name'=>$name,
					'time'=>$time,
					'action'=>$actor_action,
					
				) );
		$subject_teacher_activity = SubjectActivity::where([
						'school_id'=>$school_id, 
						'subject_teacher_id'=>$subject_teacher_id
						
					])->whereDate('created_at', '=', $today)->first();
		
		if ( $subject_teacher_activity ) {
			$action_details = $action.'~'.$subject_teacher_activity->action_details;
			
		}else {
			$subject_teacher_activity = new SubjectActivity();
			$action_details = $action;
		}
		$subject_teacher_activity->school_id = $school_id;
		$subject_teacher_activity->subject_teacher_id = $subject_teacher_id;
		$subject_teacher_activity->action_details = $action_details;
		$subject_teacher_activity->save();
    }
}
