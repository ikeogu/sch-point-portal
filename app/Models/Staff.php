<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Staff extends Model
{
    /**
     * @var string
     */
    use SoftDeletes;
    protected $table = 'staff';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'school_id',
        'staff_level_id',
        'job_type',
        'is_cv_submitted',
        'is_edu_cert_submitted',
        'is_exp_cert_submitted',
        'is_active'
    ];


    public function classTeachers()
    {
        return $this->hasMany(ClassTeacher::class, 'teacher_id', 'id');
    }

    public function subjectTeachers()
    {
        return $this->hasMany(SubjectTeacher::class, 'teacher_id', 'id');
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     *The return value is in array form and a foreach loop should be used to fetch each element
     */
    public function staffSalaryPayment()
    {
        return $this->hasMany(StaffSalaryPayment::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     *The return value is in array form and a foreach loop should be used to fetch each element
     */
    public function staffRole()
    {
        return $this->hasMany(StaffRole::class);
    }

    public function staffLevel()
    {
        return $this->belongsTo(StaffLevel::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function registerStaff($request)
    {
        $staff = Staff::where('user_id', $request->user_id)->first();

        if (!$staff) {
            $staff = new Staff();
            $staff->school_id = $request->school_id;
            $staff->user_id = $request->user_id;
            $staff->job_type = $request->job_type;
            $staff->is_cv_submitted = $request->is_cv_submitted;
            $staff->is_edu_cert_submitted = $request->is_edu_cert_submitted;
            $staff->is_exp_cert_submitted = $request->is_exp_cert_submitted;

            $staff->save();
        }


        return $staff->id;
    }
}
