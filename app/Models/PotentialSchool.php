<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PotentialSchool extends Model
{
    //use SoftDeletes;
    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];

    public function lga()
    {
        return $this->belongsTo(LocalGovernmentArea::class, 'lga_id', 'id');
    }


    public function getFolderKey($id)
    {
        $folder_key = $this->findOrFail($id);
        return $folder_key->folder_key;
    }

    public function registerSchool($request, $registrar_id)
    {

        $school = PotentialSchool::where('name', $request->name)->orWhere('email', $request->email)->orWhere('slug', $request->slug)->first();

        if (!$school) {
            // $inputs = $request->all();
            $school = new PotentialSchool();

            // $logo = "img/logo.png";
            // $mime = "image/png";
            $folder_key = time();
            // admin info
            $school->admin_first_name = $request->admin_first_name;
            $school->admin_last_name = $request->admin_last_name;
            $school->admin_email = $request->admin_email;
            $school->admin_phone1 = $request->admin_phone1;
            $school->admin_phone2 = $request->admin_phone2;
            $school->admin_gender = $request->admin_gender;

            $school->name = $request->name;
            $school->slug = strtoupper($request->slug);
            $school->address = $request->address;
            $school->lga = $request->lga_id;
            $school->estimated_no_of_students = $request->estimated_no_of_students;
            $school->website = $request->website;
            $school->email = $request->email;
            $school->phone = $request->phone;
            $school->sub_domain = $request->sub_domain;
            if (isset($request->nursery) && $request->nursery === true) {
                $school->nursery = '1';
            }
            if (isset($request->pry) && $request->pry === true) {
                $school->pry = '1';
            }
            if (isset($request->secondary) && $request->secondary === true) {
                $school->secondary = '1';
            }
            $school->curriculum = $request->curriculum;
            $school->registered_by = $registrar_id; // the registrar is the Partner

            // $school->logo = $logo;
            // $school->mime = $mime;
            // $school->preferred_template = 'custom_one';
            $school->folder_key = $folder_key;
            // $school->current_session = SSession::orderBy('id', 'DESC')->first()->id; //this is the default value and can be changed
            // $school->current_term = Term::first()->id; //this is the default value and can be changed
            $school->save();

            return $school;
        }
        return 'Exist';
    }
}
