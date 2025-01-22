<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UniqNumGen extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'school_id',
        'prefix_staff',
        'prefix_student',
        'prefix_parent',
        'next_student_no',
        'next_parent_no',
        'next_staff_no'
    ];
    //
    public function generateUsername($school_id,$role)
    {
        $uniq_num = UniqNumGen::where('school_id', $school_id)->first();

        switch ($role) {
            case 'student':

                $uniq_num_gen = $uniq_num->next_student_no;
                $prefix = $uniq_num->prefix_student;
                $uniq_num_gen = formatUniqNo($uniq_num_gen);

                return $prefix.$uniq_num_gen;
                break;
            case 'staff':

                $uniq_num_gen = $uniq_num->next_staff_no;
                $prefix = $uniq_num->prefix_staff;
                $uniq_num_gen = formatUniqNo($uniq_num_gen);

                return $prefix.$uniq_num_gen;

                break;
            case 'parent':

                $uniq_num_gen = $uniq_num->next_parent_no;
                $prefix = $uniq_num->prefix_parent;
                $uniq_num_gen = formatUniqNo($uniq_num_gen);

                return $prefix.$uniq_num_gen;

                break;
            case 'partner':

                $uniq_num_gen = UniqNumGen::find(1)->next_partner_no;

                $uniq_num_gen = str_replace('#', "", randomColorCode().$uniq_num_gen) ;
                //$uniq_num_gen = formatUniqNo($uniq_num_gen);

                return 'SP'.'/REF/'.$uniq_num_gen;
                break;


        }
    }

    public function updateUniqNumDb($school_id,$role)
    {
        $uniq_num_gen = UniqNumGen::where('school_id', $school_id)->first();
        switch ($role) {
            case 'student':
                $uniq_num = $uniq_num_gen->next_student_no + 1;
                $uniq_num_gen->next_student_no = $uniq_num;
                $uniq_num_gen->save();

                break;
            case 'staff':
                $uniq_num = $uniq_num_gen->next_staff_no + 1;
                $uniq_num_gen->next_staff_no = $uniq_num;
                $uniq_num_gen->save();

                break;
            case 'parent':
                $uniq_num = $uniq_num_gen->next_parent_no + 1;
                $uniq_num_gen->next_parent_no = $uniq_num;
                $uniq_num_gen->save();

                break;

            case 'partner':
                $uniq_num = $uniq_num_gen->next_partner_no + 1;
                $uniq_num_gen->next_partner_no = $uniq_num;
                $uniq_num_gen->save();

                break;
        }
    }
    public function createUniqGen($request)
    {

        $uniq_num = UniqNumGen::where('school_id', $request->school_id)->first();
        if (!$uniq_num) {
            $uniq_num = new UniqNumGen();
            $uniq_num->school_id = $request->school_id;
            $uniq_num->prefix_staff = $request->slug.'/';
            $uniq_num->prefix_student = $request->slug.'/STU/';
            $uniq_num->prefix_parent = $request->slug.'/GUA/';
            $uniq_num->next_student_no = 1;
            $uniq_num->next_parent_no = 1;
            $uniq_num->next_staff_no = 1;
            $uniq_num->save();
        }
        return $uniq_num;

    }
}
