<?php

namespace App\Http\Controllers;

use App\Models\GroupOfSchool;
use App\Http\Requests\SchoolRequest;
use App\Models\School;
use App\Models\User;
use App\Models\Staff;
use App\Models\StaffRole;
use App\Models\PartnerSchool;
use App\Models\PotentialSchool;
use App\Models\UniqNumGen;
use App\Models\LocalGovernmentArea;
use App\Mail\AdminRegistrationConfirmation;
use App\Models\Alumni;
use App\Models\Country;
use App\Models\Guardian;
use App\Models\Student;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class SchoolsController extends Controller
{
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $user = $this->getUser();
        $schools = [];
        if ($user->isSuperAdmin() || $user->hasPermission('can view all schools')) {

            $schools = School::with('package.packageModules.module')->get();
        }
        // $group_of_schools = GroupOfSchool::orderBy('name')->get();
        return response()->json(compact('schools'));
    }
    public function partnerSchools()
    {
        $user = $this->getUser();
        $schools = [];
        $partner_schools = $user->partnerSchools()->with('school.package.packageModules.module')->get();
        if ($partner_schools->isNotEmpty()) {

            foreach ($partner_schools as $partner_school) {
                $schools[] = $partner_school->school;
            }
        }
        return response()->json(compact('schools'));
    }
    public function activeSchools()
    {
        $schools = School::where('is_active', '1')->get();
        // $group_of_schools = GroupOfSchool::orderBy('name')->get();
        return response()->json(compact('schools'));
    }

    /**
     * This manages privileges based on roles
     *
     * @return \Illuminate\Http\Response
     */
    public function fetchSchoolCommunity()
    {
        $school_id = $this->getSchool()->id;
        $students = Student::with('user')->where('school_id', $school_id)->get();
        $staff = Staff::with('user')->where('school_id', $school_id)->get();
        $users = $students->merge($staff);
        return response()->json(compact('users'), 200);
    }
    public function create()
    {


        $countries = Country::with('states.lgas')->orderBy('country_name')->get();
        $selected_country = Country::with('states.lgas')->where('country_name', 'Nigeria')->first();
        //////////////////////////////////////////////////////////////////////////


        return  $this->render(compact('countries', 'selected_country'));
        /*}
        return redirect()->route('student_reg_pin');*/
    }


    public function saveGeneralSettings(Request $request, School $school)
    {
        // $school = $this->getSchool();
        if (isset($request->navbar_bg)) {

            $school->navbar_bg = $request->navbar_bg;
        }
        if (isset($request->sidebar_bg)) {

            $school->sidebar_bg = $request->sidebar_bg;
        }
        // $school->main_bg = $request->main_bg;
        // $school->logo_bg = $request->logo_bg;
        // $school->display_student_position = $request->display_student_position;

        $school->save();
        return 'success';
    }

    /**
     * @param SchoolRequest $request
     * @param School $school
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, School $school)
    {
        $user_obj = new User();
        $staff_obj = new Staff();
        $staff_role_obj = new StaffRole();
        $partner_sch_obj = new PartnerSchool();
        $uniq_num_gen_obj = new UniqNumGen();

        $registered_school = $school->registerSchool($request);
        $request->school_id = $registered_school->id;

        $uniq_num_gen_obj->createUniqGen($request);

        // $uniq_num_gen_obj->school_id = $request->school_id;
        // $uniq_num_gen_obj->prefix_staff = $request->slug;
        // $uniq_num_gen_obj->prefix_student = $request->slug . '/STU/';
        // $uniq_num_gen_obj->prefix_parent = $request->slug . '/GUA/';
        // $uniq_num_gen_obj->next_student_no = 1;
        // $uniq_num_gen_obj->next_parent_no = 1;
        // $uniq_num_gen_obj->next_staff_no = 1;
        // $uniq_num_gen_obj->save();


        $username = $uniq_num_gen_obj->generateUsername($request->school_id, 'staff');
        $request->username = $username;

        //save the school admin details and create his admin role
        list($user, $status) = $user_obj->saveUserAsAdmin($request);
        $user->school_name = $registered_school->name;
        $user->username = $user->username; //$username;
        $user->raw_password = 'password'; //$user->username;
        $request->user_id = $user->id;

        $partner = $this->getPartner();
        //add this school to our partner who registered it if applicable
        if (isset($request->referral_id) && $request->referral_id != "") {
            # code...
            $partner_id = $request->referral_id;
            $partner_sch_obj->addPartnerSchool($partner_id, $request->school_id);
        }

        if ($status == "new_entry") {

            $uniq_num_gen_obj->updateUniqNumDb($request->school_id, 'staff');

            $staff_id = $staff_obj->registerStaff($request);

            $staff_role_id = $staff_role_obj->addStaffRole($staff_id, $registered_school->id, 'admin');


            //send activation email
            \Mail::to($user)->send(new AdminRegistrationConfirmation($user));
            // Flash::success('Registration successfully. Please login to your mail: ' . $request->email . ' for more information.');
        } else if ($status == "exists") {
            // Flash::warning('School Registered. However user already exist.');
        } else {
            // Flash::error('An Error Occured. Please try again');
        }

        return redirect()->route('register_school_form');
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function edit($id)
    {
        try {
            $school = School::findOrFail($id);
            //echo $school->name;
            return $this->render('schools.edit', compact('school'));
        } catch (ModelNotFoundException $ex) {
            // Flash::error('Error: ' . $ex->getMessage());
            return redirect()->route('schools.index');
        }
    }

    /**
     * @param SchoolRequest $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $school = School::findOrFail($id);

            $inputs = $request->all();

            if ($request->hasFile('logo')) {
                // Unlink the old image
                Storage::disk('public')->delete($school->logo);

                $mime = $request->file('logo')->getClientMimeType();
                $name = "school_" . time() . "." . $request->file('logo')->guessClientExtension();
                $logo = $request->file('logo')->storeAs('schools', $name, 'public');
                $inputs['logo'] = $logo;
                $inputs['mime'] = $mime;
            }
            $inputs['curriculum'] = implode('~', $request->curriculum);
            $school->update($inputs);
            // Flash::success('School information updated successfully');
            return redirect()->route('schools.index');
        } catch (ModelNotFoundException $ex) {
            // Flash::error('Error: ' . $ex->getMessage());
            return redirect()->route('schools.index');
        }
    }

    public function show(School $school)
    {
        $school_id = $school->id;
        $total_students = Student::ActiveAndSuspended()->where(['school_id' => $school_id])->count();
        $active_students = Student::ActiveStudentOnly()->where(['school_id' => $school_id])->count();
        $suspended_students = Student::SuspendedStudentOnly()->where(['school_id' => $school_id])->count();
        $withdrawn_students = Student::WithdrawnStudentOnly()->where(['school_id' => $school_id])->count();
        $alumni = Alumni::where(['school_id' => $school_id])->count();

        $active_male = Student::ActiveStudentOnly()->join('users', 'users.id', 'students.user_id')
            ->where(['students.school_id' => $school_id, 'users.gender' => 'male'])->count();

        $active_female = Student::ActiveStudentOnly()->join('users', 'users.id', 'students.user_id')
            ->where(['students.school_id' => $school_id, 'users.gender' => 'female'])->count();

        $totalStaff = Staff::where(['school_id' => $school_id])->count();
        $totalGuardian = Guardian::where('school_id', $school_id)->count();
        $school = $school->with('package', 'students.user', 'students.studentGuardian.guardian.user', 'staff.user')->find($school->id);

        return response()->json(compact('total_students', 'active_students', 'active_male', 'active_female', 'suspended_students', 'withdrawn_students', 'alumni', 'totalStaff', 'totalGuardian', 'school'));
    }

    public function checkDuplicateSchool(Request $request)
    {
        $field = $request->field;
        $value = $request->value;

        $school = School::where($field, $value)->first();
        if ($school) {
            return 'exist';
        }
        return 'not found';
    }

    public function registerPotentialSchool(Request $request)
    {
        // $user_obj = new User();
        // $staff_obj = new Staff();
        // $staff_role_obj = new StaffRole();
        // // $partner_sch_obj = new PartnerSchool();
        // $uniq_num_gen_obj = new UniqNumGen();
        $school = new PotentialSchool();
        $user = $this->getUser();
        $user_id = NULL;
        if ($user) {
            $user_id = $user->id;
        }
        $registered_school = $school->registerSchool($request, $user_id);
        if ($registered_school === 'Exist') {
            return response()->json(['message' => "This School's information already exist"], 500);
            # code...
        }
        // $request->school_id = $registered_school->id;
        // send mail to admin

        return response()->json(compact('registered_school'), 200);
    }
    public function potentialSchools()
    {

        //return "These are potential schools we have";

        $user = $this->getUser();
        $potential_schools = [];
        if ($user->isSuperAdmin() || $user->hasPermission('can view all schools')) {
            $potential_schools = PotentialSchool::where('is_active', '0')->orderBy('created_at', 'DESC')->get();
        }

        return response()->json(compact('potential_schools'));
    }
    public function partnerPotentialSchools()
    {

        //return "These are potential schools we have";

        $user = $this->getUser();
        $potential_schools = $user->potentialSchools()->where('is_active', '0')->orderBy('created_at', 'DESC')->get();

        return response()->json(compact('potential_schools'));
    }

    public function deletePotentialSchool(Request $request)
    {
        $potential_school = PotentialSchool::find($request->school_id);
        $potential_school->delete();
        return $this->potentialSchools();
    }

    public function confirmPotentialSchool(Request $request)
    {
        $potential_school = PotentialSchool::find($request->school_id);

        $request->name = $potential_school->name;
        $request->slug = $potential_school->slug;
        $request->address = $potential_school->address;
        $request->lga_id = $potential_school->lga_id;
        $request->school_email = $potential_school->email;
        $request->school_phone = $potential_school->phone;
        $request->sub_domain =  $potential_school->sub_domain;
        $request->curriculum = $potential_school->curriculum;
        $request->nursery = $potential_school->nursery;
        $request->primary =  $potential_school->pry;
        $request->secondary =  $potential_school->secondary;


        $request->first_name = $potential_school->admin_first_name;
        $request->last_name = $potential_school->admin_last_name;
        $request->email = $potential_school->admin_email;
        $request->phone1 = $potential_school->admin_phone1;
        $request->phone2 = $potential_school->admin_phone2;
        $request->gender = $potential_school->admin_gender;



        $school = new School();
        $user_obj = new User();
        $staff_obj = new Staff();
        $staff_role_obj = new StaffRole();
        $partner_sch_obj = new PartnerSchool();
        $uniq_num_gen_obj = new UniqNumGen();

        $registered_school = $school->registerSchool($request);
        $request->school_id = $registered_school->id;

        $uniq_num_gen_obj->createUniqGen($request);

        $username = $uniq_num_gen_obj->generateUsername($registered_school->id, 'staff');
        $request->username = $username;

        //save the school admin details and create his admin role
        list($user, $status) = $user_obj->saveUserAsAdmin($request);
        $user->syncRoles(['admin']);
        $user->school_name = $registered_school->name;
        $user->username = $user->username; //$username;
        $user->raw_password = $user->username;
        $request->user_id = $user->id;

        //add this school to our partner who registered it if applicable
        if ($potential_school->registered_by != NULL) {
            # code...
            $partner_id = $potential_school->registered_by;
            $partner_sch_obj->addPartnerSchool($partner_id, $registered_school->id);
        }

        if ($status == "new_entry") {
            //delete the potential school
            $potential_school->delete();

            $uniq_num_gen_obj->updateUniqNumDb($registered_school->id, 'staff');

            $staff_id = $staff_obj->registerStaff($request);

            $staff_role_id = $staff_role_obj->addStaffRole($staff_id, $registered_school->id, 'admin');


            //send activation email
            \Mail::to($user)->send(new AdminRegistrationConfirmation($user));
            // Flash::success('Registration successfully. Please login to your mail: ' . $request->email . ' for more information.');
        } else if ($status == "exists") {
            // Flash::warning('School Registered. However user already exist.');
        } else {
            // Flash::error('An Error Occured. Please try again');
        }

        // return redirect()->route('schools.index');
    }

    public function updateLogo(Request $request)
    {
        $school = $this->getSchool();
        if ($request->file('sch_logo') != null && $request->file('sch_logo')->isValid()) {


            $mime = $request->file('sch_logo')->getClientMimeType();

            if ($mime == 'image/png' || $mime == 'image/jpeg' || $mime == 'image/jpg' || $mime == 'image/gif') {
                // delete older ones
                if (Storage::disk('public')->exists($school->logo)) {
                    Storage::disk('public')->delete($school->logo);
                }
                $name = "school_logo_" . time() . "." . $request->file('sch_logo')->guessClientExtension();
                $folder_key = $school->folder_key;
                $folder = "schools/" . $folder_key;
                $logo = $request->file('sch_logo')->storeAs($folder, $name, 'public');

                $school->logo = $logo;
                $school->mime = $mime;

                if ($school->save()) {
                    return $school->logo;
                }
            }
        }
        return 'Invalid file uploaded';
    }
    public function toggleSchoolNonPaymentSuspension(Request $request)
    {
        $status = $request->status;
        $school_id = $request->school_id;
        $school = School::find($school_id);
        $school->suspended_for_nonpayment = $status;
        $school->save();

        return 'success';
    }
    public function setArm(Request $request)
    {
        $arm = $request->arm;
        $value = $request->value;
        $school_id = $request->school_id;
        $school = School::find($school_id);
        $school->$arm = $value;
        $school->save();

        return 'success';
    }
}
