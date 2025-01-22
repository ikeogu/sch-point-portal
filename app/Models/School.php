<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'sub_domain',
        'phone',
        'lga_id',
        'address',
        'type',
        'nursery',
        'pry',
        'secondary',
        'logo',
        'mime',
        'preferred_template',
        'folder_key',
        'is_active'
    ];

    public function package()
    {
        return $this->belongsTo(Package::class);
    }
    public function students()
    {
        return $this->hasMany(Student::class);
    }
    public function staff()
    {
        return $this->hasMany(Staff::class);
    }

    public function groupOfSchool()
    {
        return $this->belongsTo(GroupOfSchool::class, 'group_of_school_id', 'id');
    }

    public function lga()
    {
        return $this->belongsTo(LocalGovernmentArea::class, 'lga_id', 'id');
    }
    public function grades()
    {
        return $this->hasMany(Grade::class);
    }

    public function currentTerm()
    {
        return $this->belongsTo(Term::class, 'current_term', 'id');
    }

    public function currentSession()
    {
        return $this->belongsTo(SSession::class, 'current_session', 'id');
    }

    public function getFolderKey($id)
    {
        $folder_key = $this->findOrFail($id);
        return $folder_key->folder_key;
    }

    public function registerSchool($request)
    {

        $school = School::where('name', $request->name)->orWhere('email', $request->school_email)->first();

        if (!$school) {
            // $inputs = $request->all();
            $school = new School();

            $logo = "img/logo.png";
            $mime = "image/png";
            $folder_key = time();
            if ($request->file('logo') != null && $request->file('logo')->isValid()) {
                $mime = $request->file('logo')->getClientMimeType();
                $name = "school_logo." . $request->file('logo')->guessClientExtension();
                $folder_key = time();
                $folder = "schools/" . $folder_key;
                $logo = $request->file('logo')->storeAs($folder, $name, 'public');
            }

            //$inputs['curriculum'] = implode('~', $request->curriculum);
            $school->name = $request->name;
            $school->slug = $request->slug;
            $school->address = $request->address;
            $school->lga_id = $request->lga_id;
            $school->email = $request->school_email;
            $school->phone = $request->school_phone;
            $school->sub_domain = $request->sub_domain;
            if (isset($request->nursery)) {
                $school->nursery = $request->nursery;
            }
            if (isset($request->primary)) {
                $school->pry = $request->primary;
            }
            if (isset($request->secondary)) {
                $school->secondary = $request->secondary;
            }
            $school->curriculum = $request->curriculum;
            $school->logo = $logo;
            $school->mime = $mime;
            $school->preferred_template = 'custom_one';
            $school->folder_key = $folder_key;
            $school->current_session = SSession::orderBy('id', 'DESC')->first()->id; //this is the default value and can be changed
            $school->current_term = Term::first()->id; //this is the default value and can be changed
            $school->save();
        }
        return $school;
    }
}
