<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'school_id',
        'teacher_id',
        'subject_teacher_id',
        'title',
        'material'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function school() {
        return $this->belongsTo(School::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function subjectTeacher() {
        return $this->belongsTo(SubjectTeacher::class);
    }

    

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function teacher()
    {
        return $this->belongsTo(Staff::class, 'teacher_id', 'id');
    }

    /**
    *teacherMaterials() method
    *@param $teacher_id gotten from $staff_id of Staff table
    *@return $details
    */
    public function teacherMaterialsDetails($materials)
    {
        
        foreach( $materials as $material):

            $detail = SubjectTeacher::find($material->subject_teacher_id);
            $subject_id = $detail->subject_id;
            $class_id = $detail->classTeacher->class_id;
            
            $subject = Subject::find($subject_id);
            $class = CClass::find($class_id);
            $level = Level::find($class->level);
            if($material->teacher_id != NULL){
                $staff = Staff::find($material->teacher_id);
                $material->teacher = $staff->user;
            }
            

            $material->subject = $subject;
            $material->class = $class;
            $material->level = $level;
            
        endforeach;

        return $materials;
    }

}
