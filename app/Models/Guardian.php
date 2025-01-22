<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Guardian extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'school_id',
        'first_name',
        'last_name',
        'relation',
        'gender',
        'present_address',
        'permanent_address',
        'email',
        'phone_no',
        'avatar',
        'mime',
        'is_active'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function school()
    {
        return $this->belongsTo(School::class);
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function guardianStudents()
    {
        return $this->hasMany(GuardianStudent::class, 'guardian_id', 'id');
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */

    public function saveGuardianStudent($guardianId, $studentId, $relationship)
    {
        $guardian_student = GuardianStudent::where('student_id', $studentId)->first();
        if (!$guardian_student) {
            $guardian_student = new GuardianStudent();
        }



        //save new relationship
        $guardian_student->guardian_id = $guardianId;
        $guardian_student->student_id = $studentId;
        $guardian_student->relationship = $relationship;
        $guardian_student->save();
    }
    public function saveGuardianInfo($request)
    {
        //$this->student_id = $request->student_id;


        $school_id = $request->school_id;
        $user_id = $request->parent_user_id;
        $occupation = $request->occupation;
        if ($occupation == 'Others') {
            $occupation = ($request->other_occupation != "") ? $request->other_occupation : 'Others';
        }
        $student_id = $request->student_id;
        $relationship = ($request->relation) ? $request->relation : 'Sponsor';


        //check if this entry exists
        $guardian = Guardian::where('user_id', $request->parent_user_id)->first();

        if (!$guardian) {

            $guardian = new Guardian();
            $guardian->school_id = $school_id;
            $guardian->user_id = $user_id;
            $guardian->occupation = $occupation;
            $guardian->save();
        }
        $guardian_id = $guardian->id;
        //populate guardian_students table

        $guardian_student = GuardianStudent::where('student_id', $student_id)->first();
        if (!$guardian_student) {
            $guardian_student = new GuardianStudent();
        }



        //save new relationship
        $guardian_student->guardian_id = $guardian_id;
        $guardian_student->student_id = $student_id;
        $guardian_student->relationship = $relationship;
        $guardian_student->save();
    } //end of function

    function fetchGuardianDetails($id)
    {

        try {

            $guardian_details = Guardian::with('guardianStudents.student')->find($id);



            $wards = [];
            foreach ($guardian_details->guardianStudents as $guardianWard) {
                $student = $guardianWard->student; //Student::find($ward_id);
                $student->parent_relationship = $guardianWard->relationship;


                $wards[$student->id] = $student;
            }

            $guardian_details->wards = $wards;

            return $guardian_details;
        } catch (\Exception $e) {

            return false;
        }
    }
}
