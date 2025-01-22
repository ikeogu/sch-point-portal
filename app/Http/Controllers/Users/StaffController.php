<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\Request;

use App\Models\GroupOfSchool;
use App\Models\RegistrationPin;
use App\Models\Result;
use App\Models\Role;
use App\Models\School;
use App\Models\SSession;
use App\Models\Staff;
use App\Models\StaffRole;
use App\Models\User;
use App\Models\Teacher;
use App\Models\StaffLevel;
use App\Models\Term;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Storage;
use Laracasts\Flash\Flash;
use Hash;

class StaffController extends Controller
{
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        set_time_limit(0);
        $school = $this->getSchool();
        $staff = Staff::with(['user.roles', 'user.state', 'user.lga'/*'user.country.states.lgas',*/])->where('school_id', $school->id)->get();
        foreach ($staff as $each_staff) {
            if ($each_staff->user) {

                $each_staff->user->permissions = $each_staff->user->allPermissions();
            }
        }

        return $this->render(compact('staff'));
    }

    public function fetchStaff()
    {
        $school = $this->getSchool();
        $staff = Staff::with(['user'])->where('school_id', $school->id)->get();

        return $this->render(compact('staff'));
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create(Request $request)
    {
        if (isset($request->school_id) && $request->school_id !== '') {
            $school_id = $request->school_id;
        } else {

            $school_id = $this->getSchool()->id;
        }
        $username = $this->generateUsername($school_id, 'staff');
        $staff_roles = Role::where('role_type', 'staff')->whereIn('school_id', [0, $school_id])->get();
        return response()->json(compact('username', 'staff_roles'));
    }

    /**
     * @param UserRequest $request
     * @param Staff $staff
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, Staff $staff,  User $user)
    {
        // return $request;
        // Create staff account
        $request->school_id = $this->getSchool()->id;
        try {
            $saved_user = $user->saveUserAsStaff($request);
            $request->user_id = $saved_user->id;
            // asssign the roles to the user
            $saved_user->syncRoles($request->roles);
            $staff_id = $staff->registerStaff($request);
            // $staff_role_obj = new StaffRole();
            // $staff_role_obj->addStaffRole($staff_id, $request->school_id, $request->roles);
            $this->updateUniqNumDb($this->getschool()->id, 'staff');

            $action = "Registered " . $request->first_name . " " . $request->last_name . " as new staff";
            $this->auditTrailEvent($request, $action);
            //$new_user = User::find($request->student_user_id);
            //$all_staff = User::where('role', 'staff')->get();
            //$user->notify(new NewRegistration($user));
            //Notification::send($all_staff, new NewRegistration($new_user));
            return 'Successful';

            // Flash::success('Staff information added successfully');
        } catch (ModelNotFoundException $ex) {
            // Flash::error('Error: ' . $ex->getMessage());
        }
        return redirect()->route('staff.index');
    }

    public function storeWithPin(Request $request, Staff $staff,  User $user)
    {
        // return $request;
        // Create staff account
        // $request->school_id = $this->getSchool()->id;
        $school_id = $request->school_id;
        try {
            $request->username = $this->generateUsername($school_id, 'staff');
            $saved_user = $user->saveUserAsStaff($request, '0'); // the second parameter is the confirmation status '0' means yet to be confirmed/approved
            $request->user_id = $saved_user->id;
            // asssign the roles to the user
            $saved_user->syncRoles([$request->roles]);
            $staff_id = $staff->registerStaff($request);
            // $staff_role_obj = new StaffRole();
            // $staff_role_obj->addStaffRole($staff_id, $request->school_id, $request->roles);
            $this->updateUniqNumDb($school_id, 'staff');

            // $action = "Registered " . $request->first_name . " " . $request->last_name . " as new staff";
            // $this->auditTrailEvent($request, $action);
            //$new_user = User::find($request->student_user_id);
            //$all_staff = User::where('role', 'staff')->get();
            //$user->notify(new NewRegistration($user));
            //Notification::send($all_staff, new NewRegistration($new_user));

            // change Registration pin status to used
            // $registrationPin = RegistrationPin::find($request->pin_id);
            // if ($registrationPin) {

            //     $registrationPin->status = 'used';
            //     $registrationPin->save();
            // }
            return 'Successful';

            // Flash::success('Staff information added successfully');
        } catch (ModelNotFoundException $ex) {
            // Flash::error('Error: ' . $ex->getMessage());
        }
        return redirect()->route('staff.index');
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function edit($id)
    {
        try {
            $staff = Staff::findOrFail($id);
            $schoolsList = ['' => 'Select School'] + School::pluck('name', 'id')->all();
            return $this->render('core::staff.edit', compact('staff', 'schoolsList'));
        } catch (ModelNotFoundException $ex) {
            // Flash::error('Error: ' . $ex->getMessage());
            return redirect()->route('staff.index');
        }
    }

    /**
     * @param StaffRequest $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Staff $staff)
    {
        // $user = $this->getUser()->id;

        try {

            $staff->job_type = $request->job_type;
            $staff->is_cv_submitted = $request->is_cv_submitted;
            $staff->is_edu_cert_submitted = $request->is_edu_cert_submitted;
            $staff->is_exp_cert_submitted = $request->is_exp_cert_submitted;
            $staff->save();

            $staff_user = User::find($request->id);
            $staff_user->first_name = $request->first_name;
            $staff_user->last_name = $request->last_name;
            $staff_user->email = $request->email;
            $staff_user->address = $request->address;
            $staff_user->phone1 = $request->phone1;
            $staff_user->phone2 = $request->phone2;
            $staff_user->gender = $request->gender;
            $staff_user->religion = $request->religion;
            $staff_user->lga_id = $request->lga_id;
            $staff_user->state_id = $request->state_id;
            $staff_user->country_id = $request->country_id;
            $staff_user->dob = $request->dob;
            $staff_user->save();

            return response()->json(compact('staff_user'), 200);
        } catch (ModelNotFoundException $ex) {
            return response()->json(compact('ex'), 500);
        }
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function show($id)
    {
        $staff = Staff::with(['user.state', 'user.lga', 'classTeachers.c_class', 'subjectTeachers' => function ($q) {
            $q->orderBy('class_teacher_id');
        }, 'subjectTeachers.classTeacher.c_class', 'subjectTeachers.subject'])->findOrFail($id);
        return $this->render(compact('staff'));
    }

    public function destroy(Staff $staff)
    {
        if ($this->getStaff()->id != $staff->id) {
            // unassign subject teacher

            $user = $staff->user;
            $user->delete();
            $staff->delete();
        }

        return response()->json([], 204);
    }
    public function assignStaffRole(Request $request)
    {
        $roles = $request->roles;
        $staff_id = $request->staff_id;
        $staff = Staff::find($staff_id);

        $school = $this->getSchool();
        $school_id = $school->id;
        //$staff_id = Staff::find('pf_no', $pf_no)->first()->id;

        foreach ($roles as $role) {
            if ($role === 'proprietor') {
                if ($school->group_of_school_id !== null) {
                    // give proprietor access to the group of schools
                    $group_of_school = GroupOfSchool::find($school->group_of_school_id);
                    $group_of_school->proprietor_user_id = $staff->user_id;
                    $group_of_school->save();
                }
            }
            $staff_role_obj = new StaffRole();
            $staff_role_obj->addStaffRole($staff_id, $school_id, $role);
        }
        //$request->session()->flash('tab_display', 'assign_role');
        // Flash::success('Roles Assigned Successfully');

        return redirect()->route('staff.index');
    }

    public function unassignStaffRole(Request $request)
    {
        $school = $this->getSchool();
        $role = $request->role;
        $staff_id = $request->staff_id;
        if ($role === 'proprietor') {
            if ($school->group_of_school_id !== null) {
                // give proprietor access to the group of schools
                $group_of_school = GroupOfSchool::find($school->group_of_school_id);
                $group_of_school->proprietor_user_id = null;
                $group_of_school->save();
            }
        }

        $staff_role = StaffRole::where(['staff_id' => $staff_id, 'role' => $role])->first();

        if ($staff_role) {
            $staff_role->delete();
        }
        // Flash::error('Role Unssigned Successfully');
        if (request()->ajax()) {
            return 'true';
        }
        //$request->session()->flash('tab_display', 'assign_role');


        return redirect()->route('staff.index');
    }
    public function staffPerformanceAnalysis(Result $result, Request $request)
    {
        $grades = $this->getGrades();
        $teacher_id = $this->getStaff()->id;
        $sess_id = $this->getSession()->id;
        if (isset($request->sess_id) && $request->sess_id != "") {
            $sess_id = $request->sess_id;
        }
        if (isset($request->teacher_id) && $request->teacher_id != "") {
            $teacher_id = $request->teacher_id;
        }
        $all_sessions = SSession::orderBy('id', 'DESC')->get();
        $selected_session = SSession::find($sess_id);

        list($subject_averages, $performance_average) = $result->analyzeTeacherPerformance($teacher_id, $sess_id, $grades);

        $overall_performance = [$performance_average];
        return response()->json(compact('subject_averages', 'overall_performance', 'all_sessions', 'selected_session', 'sess_id'), 200);
    }

    public function sessionalStaffPerformance(Request $request)
    {
        $teacher_id = $this->getStaff()->id;

        $all_sessions = SSession::orderBy('id')->get();
        $terms = Term::orderBy('id')->get();
        $categories = [];
        $data = [];
        $first_term_analysis = [];
        $second_term_analysis = [];
        $third_term_analysis = [];
        $dataLabels = [
            'enabled' => true,
            //'rotation' => -90,
            'color' => '#FFFFFF',
            'align' => 'center',
            //format: '{point.y:.1f}', // one decimal
            'y' => 25, // 10 pixels down from the top
            'style' => [
                'fontSize' => '10px',
                'fontFamily' => 'Verdana, sans-serif'
            ]
        ];
        foreach ($all_sessions as $session) {
            # code...
            $sess_id = $session->id;

            if (Result::where(['recorded_by' => $teacher_id, 'sess_id' => $sess_id])->count() > 1) {
                $first_term_average = Result::where(['recorded_by' => $teacher_id, 'sess_id' => $sess_id, 'term_id' => '1'])->where('exam', '!=', null)->avg('total');
                $first_term_analysis[] = [
                    'name' => $session->name,
                    'y' => (float) sprintf("%01.1f", $first_term_average),
                    //'drilldown' => $level->level . '_absent',

                ];

                $second_term_average = Result::where(['recorded_by' => $teacher_id, 'sess_id' => $sess_id, 'term_id' => '2'])->where('exam', '!=', null)->avg('total');

                $second_term_analysis[] = [
                    'name' => $session->name,
                    'y' => (float) sprintf("%01.1f", $second_term_average),
                    //'drilldown' => $level->level . '_absent',

                ];
                $third_term_average = Result::where(['recorded_by' => $teacher_id, 'sess_id' => $sess_id, 'term_id' => '3'])->where('exam', '!=', null)->avg('total');

                $third_term_analysis[] = [
                    'name' => $session->name,
                    'y' => (float) sprintf("%01.1f", $third_term_average),
                    //'drilldown' => $level->level . '_absent',

                ];
            }




            // if($average){
            //     $categories[] = $session->name;
            //     $data[] = $average;
            // }

        }
        $series = [
            [
                'name' => 'First Term',
                //'colorByPoint' => true, //array format
                'data' => $first_term_analysis,
                //'stack' => 'student',
                //'color' => '#00c0ef ',
                'dataLabels' => $dataLabels
            ],
            [
                'name' => 'Second Term',
                //'colorByPoint' => true, //array format
                'data' => $second_term_analysis,
                //'stack' => 'student',
                //'color' => '#00c0ef ',
                'dataLabels' => $dataLabels
            ],
            [
                'name' => 'Third Term',
                //'colorByPoint' => true, //array format
                'data' => $third_term_analysis,
                //'stack' => 'student',
                //'color' => '#00c0ef ',
                'dataLabels' => $dataLabels
            ],
        ];
        return response()->json(compact('all_sessions', 'series'), 200);
    }
}
