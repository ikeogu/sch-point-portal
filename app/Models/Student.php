<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable = [
        'admission_year',
        'registration_no',
        'school_id',
        'class_id',
        'user_id',
        'is_prev_cert_submitted',
        'is_transfer_cert_submitted',
        'is_academic_transcript_submitted',
        'is_national_birth_cert_submitted',
        'is_testimonial_submitted',
        'is_active'
    ];
    // protected static function booted()
    // {
    //     static::addGlobalScope('active_suspended', function (Builder $builder) {
    //         $builder->whereIn('studentship_status', ['active', 'suspended']);
    //     });
    // }
    // public function newQuery($excludeDeleted = true)
    // {
    //     return parent::newQuery($excludeDeleted)
    //         ->whereIn('studentship_status', ['active', 'suspended']);
    // }
    public function scopeActiveStudentOnly($query)
    {
        return $query->whereStudentshipStatus('active');
    }
    public function scopeSuspendedStudentOnly($query)
    {
        return $query->whereStudentshipStatus('suspended');
    }
    public function scopeWithdrawnStudentOnly($query)
    {
        return $query->whereStudentshipStatus('left');
    }
    public function scopeActiveAndSuspended($query)
    {
        return $query->whereStudentshipStatus('active')->orWhere('studentship_status', 'suspended');
    }
    public function scopeActiveSuspendedAndLeft($query)
    {
        return $query->whereStudentshipStatus('active')->orWhere('studentship_status', 'suspended')->orWhere('studentship_status', 'left');
    }

    // protected static function booted()
    // {
    //     static::addGlobalScope('studentship_status', function (Builder $builder) {
    //         $builder->where('studentship_status', 'active')->orWhere('studentship_status', 'suspended');
    //     });
    // }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function studentGuardian()
    {
        return $this->hasOne(GuardianStudent::class, 'student_id', 'id');
    }
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
    public function classTeacher()
    {
        return $this->belongsTo(ClassTeacher::class, 'class_id', 'id');
    }

    public function myClasses()
    {
        return $this->hasMany(StudentsInClass::class, 'student_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function levelAdmitted()
    {
        return $this->belongsTo(Level::class, 'level_admitted', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function currentStudentLevel()
    {
        return $this->belongsTo(Level::class, 'current_level', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function session()
    {
        return $this->belongsTo(SSession::class, 'admission_sess_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     *The return value is in array form and a foreach loop should be used to fetch each element
     */
    public function feePaymentMonitor()
    {
        return $this->hasMany(FeePaymentMonitor::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     *The return value is in array form and a foreach loop should be used to fetch each element
     */
    public function behaviors()
    {
        return $this->hasMany(Behavior::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     *The return value is in array form and a foreach loop should be used to fetch each element
     */
    public function skills()
    {
        return $this->hasMany(Skill::class);
    }

    public function behavior()
    {
        return $this->hasOne(Behavior::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     *The return value is in array form and a foreach loop should be used to fetch each element
     */
    public function skill()
    {
        return $this->hasOne(Skill::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     *The return value is in array form and a foreach loop should be used to fetch each element
     */
    public function results()
    {
        return $this->hasMany(Result::class);
    }

    public function assignment()
    {
        return $this->belongsToMany(Assignment::class, 'assignment_students', 'student_id', 'assignment_id')->withPivot('student_id', 'assignment_id', 'is_submitted', 'date', 'assignment');
    }

    /**
     *Method to save student informaiton in the students table
     * @param $request from submitted form and an alternative $action
     * @return $student id for further processes
     */
    public function saveStudentInfo($request, $action = "save")
    {


        if ($action == 'update') {
            //this is the students.id from students table
            $id = $request->student_id;
            $student = Student::findOrFail($id);
            //$student->class_id = $request->class_id;
            $student->admission_year = $request->admission_year;
            $student->current_level = $request->level_id;
            $student->admission_sess_id = $request->admission_sess_id;
            $student->is_prev_cert_submitted = $request->is_prev_cert_submitted;
            $student->is_transfer_cert_submitted = $request->is_transfer_cert_submitted;
            $student->is_academic_transcript_submitted = $request->is_academic_transcript_submitted;
            $student->is_national_birth_cert_submitted = $request->is_national_birth_cert_submitted;
            $student->is_testimonial_submitted = $request->is_testimonial_submitted;
            //get the users.id from the students table

            $student->save();
            return $student->id;
        } else {
            $this->user_id = $request->student_user_id;
            $this->school_id = $request->school_id;
            $this->class_id = $request->class_id;
            $this->registration_no = $request->registration_no;
            $this->level_admitted = $request->level_id;
            $this->current_level = $request->level_id;
            $this->admission_year = $request->admission_year; //date('Y', strtotime('now'));
            $this->admission_sess_id = $request->admission_sess_id;
            $this->is_prev_cert_submitted = $request->is_prev_cert_submitted;
            $this->is_transfer_cert_submitted = $request->is_transfer_cert_submitted;
            $this->is_academic_transcript_submitted = $request->is_academic_transcript_submitted;
            $this->is_national_birth_cert_submitted = $request->is_national_birth_cert_submitted;
            $this->is_testimonial_submitted = $request->is_testimonial_submitted;
            $this->save();

            return $this->id;
        }
    }

    /**
     *Method to fetch Guradian info for this student
     * @param $id: Student's unique id
     * @return $parent information collection
     */
    public function getGuardianDetails($id)
    {
        $student_guardian = GuardianStudent::where('student_id', $id)->first();

        $parent = "";
        if ($student_guardian) {
            $parent = $student_guardian->guardian;
        }
        return  $parent;
        /*$parent = Guardian::where('ward_ids', 'LIKE', '%~'.$id.'~%')->first();
        if ($parent) {
            $ward_details = explode('~', $parent->ward_details);
            foreach ($ward_details as $each_ward_relationship) {

                $relationships = json_decode($each_ward_relationship, 1);
                foreach ($relationships as $key=>$value) {
                    if($key == 'relationship_'.$id){
                        $parent->relationship = $value;
                    }
                }


            }
        }*/
    }

    /**
     *Method to fetch Class Teacher info
     * @param $class_teacher_id
     * @return $class_teacher's full information collection
     */
    public function getClassTeacherDetails($id)
    {
        $class_teacher = ClassTeacher::find($id);

        $teacher = "";
        if ($class_teacher->teacher_id != null) {
            $user_id = $class_teacher->staff->user_id;
            $teacher = User::find($user_id);
        }


        //$level = Level::find($class_teacher->level_id);


        $class_teacher->teacher = $teacher;
        //$class_teacher->level = $level;

        return $class_teacher;
    }

    /**
     *Method to fetch Subject Teacher info
     * @param $class_teacher_id
     * @return $subject_teachers' full information collection
     */
    public function getSubjectTeacherDetails($class_teacher_id)
    {
        $subject_teachers = SubjectTeacher::where('class_teacher_id', $class_teacher_id)
            ->get();
        foreach ($subject_teachers as $subject_teacher) :
            if ($subject_teacher->staff) {
                $user_id = $subject_teacher->staff->user_id;

                $teacher = User::find($user_id);

                $subject_teacher->teacher = $teacher;
            }

        endforeach;

        return $subject_teachers;
    }
    /**
     *Method to fetch Student info
     * @param $id Student's Id
     * @param $class_teacher_id
     * @return array listing $student, $parant, $class and $subject details
     */
    // public function getStudentDetails($id, $class_teacher_id)
    // {
    //     $student = Student::find($id);


    //     //Get Parent details for this student
    //     $parent = $this->getGuardianDetails($id);

    //     //get class/class_teacher details
    //     $class = $this->getClassTeacherDetails($class_teacher_id);


    //     //Get Teacher/Subjects details for this student
    //     $subject = $this->getSubjectTeacherDetails($class->id);

    //     return array($student, $parent, $class, $subject);
    // }

    public function getStudentDetails($school, $student_id, $class_teacher_id)
    {
        $result = new Result();
        $curriculum_level_groups = CurriculumLevelGroup::where('curriculum', $school->curriculum)->get();
        $student = Student::with(['school.lga', 'studentGuardian.guardian.user', 'user.state', 'user.lga', 'classTeacher.c_class',  'classTeacher.level', 'classTeacher.staff.user', 'classTeacher.subjectTeachers.staff.user', 'behaviors', 'skills', 'results.subjectTeacher.subject'])->find($student_id);


        //get class/class_teacher details
        // $student->class_teacher = $this->getClassTeacherDetails($class_teacher_id);


        //Get Teacher/Subjects details for this student
        // $student->subject_teachers = $this->getSubjectTeacherDetails($class_teacher_id);

        $subject_performances = [];
        foreach ($curriculum_level_groups as $curriculum_level_group) {
            $curriculum_level_group_subjects = Subject::where('curriculum_level_group_id', $curriculum_level_group->id)->orderBy('name')->get();

            $grades = Grade::where(['school_id' => $school->id, 'curriculum_level_group_id' => $curriculum_level_group->id])->get();

            foreach ($curriculum_level_group_subjects as $curriculum_level_group_subject) {

                $subject_avg = Result::join('subject_teachers', 'subject_teachers.id', '=', 'results.subject_teacher_id')
                    ->join('subjects', 'subject_teachers.subject_id', '=', 'subjects.id')
                    ->where(['student_id' => $student_id, 'subjects.id' => $curriculum_level_group_subject->id])->avg('total');
                if ($subject_avg < 1) {
                    break;
                }
                list($grade, $color, $grade_point) = $result->resultGrade($subject_avg, $grades);

                $curriculum_level_group_subject->avg  = sprintf("%01.1f", $subject_avg);
                $curriculum_level_group_subject->color  = $color;
                $subject_performances[] = $curriculum_level_group_subject;
            }
        }
        $student->subject_performances = $subject_performances;
        //$results = $student->results()->with('subjectTeacher.subject')->get();

        // $subjects = [];
        // foreach ($results as $result) :
        //     $subjects[$result->subjectTeacher->subject->name] = $result;
        // endforeach;
        // $student->$subjects = $subjects;
        return $student;
    }

    // public function allStudentInformation($students, $sess_id, $term_id, $school_id)
    // {

    //     foreach ($students as $student) :
    //         $student_in_class_obj = new StudentsInClass();

    //         $student->class_name = "Not Assigned";
    //         $student_in_class = $student_in_class_obj->fetchStudentInClass($student->id,  $sess_id, $term_id, $school_id);


    //         if ($student_in_class) {

    //             list($student_details, $parent, $class, $subjects) = $this->getStudentDetails($student->id, $student_in_class->class_teacher_id);

    //             $class_teacher_id = $student_in_class->class_teacher_id;

    //             $class_teacher = ClassTeacher::find($class_teacher_id);

    //             $student->class_name = $class_teacher->c_class->name;

    //             //$student->parent = $parent;
    //         }
    //         $student->parent = $this->getGuardianDetails($student->id);
    //         $assign_classes = ClassTeacher::where([
    //             'school_id' => $school_id,
    //             'level_id' => $student->current_level
    //         ])->get();

    //         $student->assign_classes = $assign_classes;




    //     endforeach;

    //     return $students;
    // }
    /**
     * Method to fetch students admission report for chart view for a particular session
     *@param $sess_id: Academic Session for which the admission report is to be fetched
     *@param $options: array of extra conditions for the query
     *@return $levels: containing number of males and females in a particular level
     */
    function fetchAdmissionReport($sess_id, $school_id, $school_type, $options = NULL)
    {
        /*if($options != NULL) {
            $data = Student::where($options)->get();
        }else{
            $data = Student::where(['admission_sess_id'=>$sess_id, 'school_id'=>$school_id])->get();
        }*/

        $levels = Level::where('school_type', $school_type)->get();

        foreach ($levels as $level) {
            $male_count = Student::join('class_teachers', 'students.class_id', '=', 'class_teachers.id')
                ->join('classes', 'class_teachers.class_id', '=', 'classes.id')
                ->join('users', 'students.user_id', '=', 'users.id')
                ->where(['students.admission_sess_id' => $sess_id, 'students.school_id' => $school_id, 'classes.level' => $level->id,  'users.gender' => 'male'])->count();

            $female_count = Student::join('class_teachers', 'students.class_id', '=', 'class_teachers.id')
                ->join('classes', 'class_teachers.class_id', '=', 'classes.id')
                ->join('users', 'students.user_id', '=', 'users.id')
                ->where(['students.admission_sess_id' => $sess_id, 'students.school_id' => $school_id, 'classes.level' => $level->id,  'users.gender' => 'female'])->count();

            $level->male_count = $male_count;
            $level->female_count = $female_count;
        }

        return $levels;
    }
}
