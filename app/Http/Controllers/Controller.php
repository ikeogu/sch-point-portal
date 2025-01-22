<?php

namespace App\Http\Controllers;

use App\Events\ClassEvent;
use App\Events\Event;
use App\Events\SubjectEvent;
use App\Http\Resources\UserResource;
use App\Models\ActivatedModule;
use App\Models\ClassTeacher;
use App\Models\Country;
use App\Models\CurriculumLevelGroup;
use App\Models\Gallery;
use App\Models\Grade;
use App\Models\Guardian;
use App\Models\Level;
use App\Models\LocalGovernmentArea;
use App\Models\News;
use App\Models\Partner;
use App\Models\Permission;
use App\Models\ResultDisplaySetting;
use App\Models\Role;
use App\Models\School;
use App\Models\SSession;
use App\Models\Staff;
use App\Models\StaffRole;
use App\Models\Student;
use App\Models\StudentsInClass;
use App\Models\Subject;
use App\Models\Term;
use App\Models\UniqNumGen;
use App\Models\User;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $user;
    protected $staff;
    protected $student;
    protected $guardian;
    protected $partner;
    protected $role;
    protected $roles = [];
    protected $school;
    protected $grades;
    protected $levels = [];
    protected $data = [];
    protected $this_term;
    protected $this_session;
    protected $admission_session;
    protected $currency = '₦'; //'&#x20A6;';

    public function __construct(Request $httpRequest)
    {
        $this->middleware(function ($request, $next) {
            if (Auth::check()) {

                // $this->resetStudentInClassTable();
                // $this->setAdminRole();
                if (($this->getUser()->role == "staff")) {


                    $school_id = $this->getStaff()->school_id;

                    $this->setSchool($school_id);
                } else if ($this->getUser()->role == "student") {

                    $school_id = $this->getStudent()->school_id;

                    $this->setSchool($school_id);
                }

                if (($this->getUser()->role == "parent") || ($this->getUser()->role == "staff")) {

                    if ($this->getGuardian()) {


                        $school_id = $this->getGuardian()->school_id;

                        $this->setSchool($school_id);
                    }
                }
            }
            return $next($request);
        });
    }

    private function resetStudentInClassTable()
    {
        $table = 'students_in_classes_old';
        $queries = DB::table($table)->get();
        foreach ($queries as $query) {
            $school_id = $query->school_id;
            $student_ids_array = explode('~', $query->student_ids);
            $class_teacher_id = $query->class_teacher_id;
            $sess_id = $query->sess_id;
            $term_id = $query->term_id;
            foreach ($student_ids_array as $student_id) {

                if ($student_id !== '') {
                    $student_in_class = StudentsInClass::where([
                        'class_teacher_id' => $class_teacher_id,
                        'sess_id' => $sess_id,
                        'student_id' => $student_id,
                        'school_id' => $school_id
                    ])->first();
                    if (!$student_in_class) {
                        $student_in_class = new StudentsInClass();
                        $student_in_class->class_teacher_id = $class_teacher_id;
                        $student_in_class->sess_id = $sess_id;
                        $student_in_class->student_id = $student_id;
                        $student_in_class->school_id = $school_id;
                        $student_in_class->save();
                    }
                }
            }
        }
    }
    public function render($data = [])
    {
        $this->data = array_merge($this->data, $data);

        $this->data['school'] = $this->getSchool();
        $this->data['current_session'] = $this->getSession();
        $this->data['current_term'] = $this->getTerm();
        $this->data['currency'] = '₦';
        // if (Auth::check()) {
        //     if ($this->getUser()->password_status == 'default') {

        //         return $this->updatePassword($this->data);
        //     }
        // }
        // if ($this->getUser()->role == 'student') {
        //     if ($this->studentAccountSuspended()) {
        //         return view('suspended.student', $this->data);
        //     }
        // }


        // if ($this->getUser()->role != 'super') {
        //     if ($this->schoolAccountSuspended()) {
        //         return view('suspended.school', $this->data);
        //     }
        // }
        // if(request()->ajax()){
        //     //this is for json response required to render frontend templates like vue
        //     return response()->json($this->data);
        // }

        //print_r($data['class']->class->name);exit;*/
        return response()->json($this->data, 200);
    }
    // public function toggleStudentNonPaymentSuspension(Request $request)
    // {
    //     $student_ids = $request->student_ids;
    //     foreach ($student_ids as $student_id) {
    //         $student = Student::find($student_id);
    //         $status = $student->studentship_status;
    //         if ($status == 1) {
    //             $student->studentship_status = 0;
    //         } else {
    //             $student->studentship_status = 1;
    //         }
    //         $student->save();
    //     }
    //     return 'success';
    // }

    public function studentAccountSuspended()
    {
        $student = $this->getStudent();
        if ($student->studentship_status !== 'active') {
            return true;
        }
        return false;
    }
    public function schoolAccountSuspended()
    {
        $school = $this->getSchool();
        if ($school->suspended_for_nonpayment === 1) {
            return true;
        }
        return false;
    }
    public function setRoles()
    {
        $school_id = $this->getSchool()->id;
        $roles = Role::where('school_id', 0)->orWhere('school_id', $school_id)->get();
        foreach ($roles as $role) {
            $role_permissions = [];
            foreach ($role->permissions as $permission) {
                $role_permissions[] = $permission->id;
            }
            $role->role_permissions = $role_permissions;
        }
        $this->roles = $roles;
    }
    public function getRoles()
    {
        $this->setRoles();
        return $this->roles;
    }
    public function getPermissions()
    {
        $permissions = Permission::orderBy('name')->get();
        return $permissions;
    }
    public function getSoftwareName()
    {
        return env("APP_NAME");
    }
    public function getAmountOld()
    {
        return $this->amount_old;
    }

    public function getAmountNew()
    {
        return $this->amount_new;
    }

    public function setUser()
    {
        $this->user  = new UserResource(Auth::user());
    }

    public function getUser()
    {
        $this->setUser();

        return $this->user;
    }
    public function setPartner()
    {
        $user = $this->getUser();
        $this->partner  = Partner::where('user_id', $user->id)->first();
    }

    public function getPartner()
    {
        $this->setPartner();

        return $this->partner;
    }
    public function setStaff()
    {

        $user = $this->getUser();

        //this updates the staff table with the user_id if not exist;
        $staff = Staff::with('user')->where('user_id', $user->id)->first();

        $this->staff = $staff;
    }
    public function getStaff()
    {
        $this->setStaff();
        return $this->staff;
    }
    public function setStudent($student_id = null)
    {

        $user = $this->getUser();
        if ($student_id) {
            $student = Student::with('user')->find($student_id);
        } else {
            $student = Student::with('user')->where('user_id', $user->id)->first();
        }

        $this->student = $student;
    }

    public function getStudent()
    {
        $this->setStudent();
        return $this->student;
    }

    public function setGuardian()
    {
        $user = $this->getUser();
        $guardian = Guardian::where('user_id', $user->id)->first();
        $this->guardian = $guardian;
    }

    public function getGuardian()
    {
        $this->setGuardian();
        return $this->guardian;
    }


    public function setSchool($id)
    {
        $school = School::findOrFail($id);
        $this->setResultSettings($school);
        $this->school = $school;
    }

    public function setResultSettings($school)
    {
        $curriculum = $school->curriculum;
        $curriculum_level_groups = CurriculumLevelGroup::where('curriculum', $curriculum)->get();
        foreach ($curriculum_level_groups as $curriculum_level_group) {
            $setting = ResultDisplaySetting::where(['school_id' => $school->id, 'curriculum_level_group_id' => $curriculum_level_group->id])->first();

            if (!$setting) {
                $setting = new ResultDisplaySetting();
                $setting->school_id = $school->id;
                $setting->curriculum_level_group_id = $curriculum_level_group->id;
                $setting->save();
            }
        }
    }

    public function getResultSettings($curriculum_level_group_id)
    {
        $school_id = $this->getSchool()->id;
        $result_settings = ResultDisplaySetting::where(['school_id' => $school_id, 'curriculum_level_group_id' => $curriculum_level_group_id])->first();

        return $result_settings;
    }

    public function getSchool()
    {
        return $this->school;
    }

    public function setGrades()
    {
        $school_id = $this->getSchool()->id;
        $grades = Grade::with('curriculumLevelGroup')->where('school_id', $school_id)->orderBy('curriculum_level_group_id')->orderBy('grade')->get();
        $this->grades = $grades;
    }

    public function getGrades()
    {
        $this->setGrades();
        return $this->grades;
    }

    public function getLevelGrades($curriculum_level_group_id)
    {
        $school_id = $this->getSchool()->id;

        $grades = Grade::with('curriculumLevelGroup')->where(['school_id' => $school_id, 'curriculum_level_group_id' => $curriculum_level_group_id])->orderBy('grade')->get();
        return $grades;
    }
    public function setLevels()
    {

        $school = $this->getSchool();
        $user = $this->getUser();
        if ($user->hasRole('admin')) {
            $levels = Level::with('classTeachers.c_class', 'levelGroup')->where('school_id', $school->id)->orderBy('id')->get();
        } else {
            $curriculum_level_group_ids_array = array_map(
                function ($role) {
                    return ($role['curriculum_level_group_ids'] !== null) ? explode('~', $role['curriculum_level_group_ids']) : [];
                },
                $user->roles->toArray()
            );
            $curriculum_level_group_id_each_array = [];

            foreach ($curriculum_level_group_ids_array as $curriculum_level_group_id_array) {
                $curriculum_level_group_id_each_array = array_merge($curriculum_level_group_id_each_array, $curriculum_level_group_id_array);
            }
            $levels = Level::with('classTeachers.c_class', 'levelGroup')->where('school_id', $school->id)->whereIn('curriculum_level_group_id', $curriculum_level_group_id_each_array)->orderBy('id')->get();
        }
        // $curriculum = $school->curriculum;
        /*$nur_levels = $pry_levels = $sec_levels = [];
        if ($school->nursery == "1") {
            $nur_levels = Level::where('curriculum', 'Nursery')->get();
        }
        if ($school->pry == "1") {
            $pry_levels = Level::where('curriculum', 'Primary')->get();
        }
        if ($school->secondary == "1") {
            $sec_levels = Level::where('curriculum', 'Secondary')->get();
        }*/


        // if ($user->hasRole('hod_sec') || $user->hasRole('principal')) {
        //     # code...
        //     if ($curriculum == 'british') {
        //         $levels = Level::where('school_id', $school->id)->whereIn('curriculum_level_group_id', ['3', '4'])->orderBy('id')->get();
        //     }
        // }
        // if ($user->hasRole('hod_pri') || $user->hasRole('head_primary')) {
        //     if ($curriculum == 'british') {
        //         $levels = Level::where('school_id', $school->id)->whereIn('curriculum_level_group_id', ['1', '2'])->orderBy('id')->get();
        //     }
        // }
        $this->levels = $levels;
    }
    public function fetchSessionAndTerm()
    {
        $school = $this->getSchool();
        $current_session = $this->getSession()->id;
        $sessions = SSession::orderBy('id', 'DESC')->get(); //SSession::where('id', '<=', $current_session)->orderBy('id', 'DESC')->get(); // SSession::orderBy('id', 'DESC')->get();
        $terms = Term::orderBy('id')->get();
        return $this->render(compact('sessions', 'terms'));
    }

    public function getLevels()
    {
        $this->setLevels();
        return $this->levels;
    }

    public function setSession()
    {
        //$session = SSession::where('is_active', '1')->first();
        //$this->this_session = $session;
        $school = $this->getSchool();
        if ($school) {
            if ($school->current_session != NULL) {

                $session = SSession::find($school->current_session);
            } else {
                $session = SSession::where('is_active', '1')->orderBy('id', 'DESC')->first();
                $school->current_session = $session->id;
                $school->save();
            }

            $this->this_session = $session;
        }
    }


    public function getSession()
    {
        $this->setSession();
        return $this->this_session;
    }

    public function setAdmissionSession()
    {
        $this->admission_session = SSession::where('is_admission_session', 'YES')->first();
    }

    public function getAdmissionSession()
    {
        $this->setAdmissionSession();
        return $this->admission_session;
    }
    public function setTerm()
    {
        //$term = Term::where('is_active', '1')->first();
        //$this->this_term = $term;
        $school = $this->getSchool();
        if ($school) {
            $session = Term::find((int)$school->current_term);

            $this->this_term = $session;
        }
    }

    public function getTerm()
    {
        $this->setTerm();
        return $this->this_term;
    }

    public function getClassTeachers()
    {
        $school_id = $this->getSchool()->id;
        $user = $this->getUser();
        // return $user->roles;
        $roles = array_map(
            function ($role) {
                return $role['name'];
            },
            $user->roles->toArray()
        );

        if (in_array('admin', $roles)) {
            $class_teachers = ClassTeacher::with(['level', 'c_class'])->where(['school_id' => $school_id])->get();
            return $class_teachers;
        }
        if (in_array('teacher', $roles)) {

            $id = $this->getStaff()->id;
            $class_teachers = ClassTeacher::with(['level', 'c_class'])->where(['teacher_id' => $id, 'school_id' => $school_id])->get();
            return $class_teachers;
        } else if (in_array('student', $roles)) {

            $id = $this->getStudent()->id;
            $student_in_class = StudentsInClass::with(['classTeacher.level', 'classTeacher.c_class'])->where(['student_id' => $id, 'school_id' => $school_id])->orderBy('id', 'DESC')->first();
            $class_teachers = [$student_in_class->classTeacher];
            return $class_teachers;
        } else {
            $user_roles = $user->roles;
            $class_teachers = new Collection();
            $curriculum_level_group_id_array = [];
            foreach ($user_roles as $user_role) {
                $curriculum_level_group_ids = $user_role->curriculum_level_group_ids;
                $curriculum_level_array = explode('~', $curriculum_level_group_ids);

                $curriculum_level_group_id_array = array_merge($curriculum_level_group_id_array, $curriculum_level_array);
            }

            foreach ($curriculum_level_group_id_array as $curriculum_level_group_id) {
                $level_class_teachers = ClassTeacher::with(['level', 'c_class'])
                    ->join('levels', 'levels.id', '=', 'class_teachers.level_id')
                    ->join('curriculum_level_groups', 'curriculum_level_groups.id', '=', 'levels.curriculum_level_group_id')
                    ->where(['levels.curriculum_level_group_id' => $curriculum_level_group_id, 'class_teachers.school_id' => $school_id])->get();

                $class_teachers = $class_teachers->merge($level_class_teachers);
            }
            return $class_teachers;
        }
    }

    public function getRelationship($staffs, $type = 'all')
    {
        if ($type != 'all') {

            $user_id = $staffs->user_id;

            $school_id = $staffs->school_id;

            $users = User::find($user_id);

            $schools = School::find($school_id);

            $staffs->user = $users;

            $staffs->school = $schools;
        } else {
            foreach ($staffs as $staff) :
                $user_id = $staff->user_id;
                $school_id = $staff->school_id;

                $users = User::find($user_id);
                $schools = School::find($school_id);

                $staff->user = $users;
                $staff->school = $schools;

            endforeach;
        }
    }

    public function setColorCode(Request $request)
    {
        //return $request->color_code;
        if ($request->option == 'grade') {
            $grade = Grade::find($request->id);
            $grade->color_code = '#' . $request->color_code;

            if ($grade->save()) {
                return 'success';
            }
        } else {
            if ($request->option == 'subject') {
                $subject = Subject::find($request->id);
                $subject->color_code = '#' . $request->color_code;

                if ($subject->save()) {
                    return 'success';
                }
            }
        }
        return 'failed';
    }


    public function uploadFile($media, $file_name, $folder_key)
    {
        $subdomain = ""; //School::where('folder_key', $folder_key)->first()->sub_domain;
        $storage_subfolder = ''; //'storage/';//$subdomain.'/storage/';

        $folder = "schools/" . $folder_key;

        $upload_directory = $storage_subfolder . $folder;

        $photo = $media->storeAs($upload_directory, $file_name, 'public');

        return $folder . '/' . $file_name;
    }

    public function auditTrailEvent($request, $action)
    {

        $this->setUser();
        $user = $this->getUser();
        if ($user) {
            if ($user->role == 'super') {
                return false;
            }
            if (($user->role == "staff")) {


                $user_school_id = Staff::where('user_id', $user->id)->first()->school_id;
            } else if ($user->role == "student") {

                $user_school_id = Student::where('user_id', $user->id)->first()->school_id;
            } else if ($user->role == "parent") {
                $user_school_id = Guardian::where('user_id', $user->id)->first()->school_id;
            }

            $request->actor_id = $user->id; //this is the id of the user in users table
            $request->actor_name = $user->first_name . ' ' . $user->last_name;
            $request->actor_role = $user->role;
            $request->school_id = $user_school_id;
            $request->action = $action;

            event(new Event($request));
        }
    }

    public function teacherStudentEventTrail($request, $action, $activity_type)
    {


        $user = $this->getUser();
        $user_school_id = $this->getSchool()->id;

        $request->actor_id = $user->id; //this is the id of the user in users table
        $request->actor_name = $user->first_name . ' ' . $user->last_name;
        $request->actor_role = $user->role;
        $request->school_id = $user_school_id;
        $request->action = $action;


        event(new Event($request));

        if ($activity_type == 'class') {
            event(new ClassEvent($request)); //log the class event
        } else {
            event(new SubjectEvent($request)); //log the subject event
        }
    }



    public function getLGAOfOrigin(Request $request)
    {
        //
        $lgas = LocalGovernmentArea::where('state_id', $request->state_id)->get();

        $selected_lgas = [];
        foreach ($lgas as $lga) :
            $selected_lgas[] = ['id' => $lga->id, 'name' => $lga->name];
        endforeach;



        $fetched_data = $selected_lgas;

        return json_encode($fetched_data);
    }
    public function news()
    {

        $news = News::orderBy('id', 'DESC')->get();
        return $news;
    }
    public function galleries()
    {

        $galleries = Gallery::where('is_show', 'yes')->orderBy('id', 'DESC')->get();
        return $galleries;
    }
    // public function categories()
    // {

    //     $categories = Category::where('id', '!=', 0)->orderBy('id')->get();

    //     foreach ($categories as $category) :

    //         $contents = Content::where('category_id', $category->id)->get();

    //         $category->contents = $contents;
    //     endforeach;
    //     return $categories;
    // }

    public function schoolsInLGA()
    {
        $this->setLga();
        $lgas = $this->getLga();

        $schools_in_lga = [];
        foreach ($lgas as $lga) :
            $schools  = School::where('lga_id', $lga->id)->orderBy('name')->get();

            if ($schools->count() > 0) {
                $schools_in_lga[$lga->name] = $schools;
            }
        endforeach;

        return $schools_in_lga;
    }

    public function uploadImageContent($request)
    {
        $request;
        $sch  = new School();
        $folder_key = $sch->getFolderKey($this->getSchool()->id);
        $today = todayDate();
        $purpose = 'assignment'; //$request->purpose;

        $folder = "schools/" . $folder_key . '/' . $purpose . '/' . $today;
        $action_by = 'Sam';
        $extension = $request->file('student_answer')->guessClientExtension();

        $name = "uploads/post_images/" . str_replace('/', '-', $action_by) . "." . $extension;

        $this->validate($request, [
            'student_answer' => 'mimes:jpeg,jpg,png'
        ]);

        $file = $request->file('student_answer');
        $filename = $file->getClientOriginalName();

        //$year = Carbon::now()->year;
        $imagePath = $folder; //"/uploads/post_images/{$year}/";

        /*if (file_exists(public_path($imagePath) . $filename)) {
            $filename = Carbon::now()->timestamp . '.' . $filename;
        }*/

        $file->move(public_path() . $imagePath, $filename);

        $url = $imagePath . $filename;

        echo "<script>window.parent.CKEDITOR.tools.callFunction(1,'{$url}','')</script>";
    }

    public function updatePassword($data)
    {

        $user = $this->getUser();

        $this->data = $data;
        $this->data['id'] = $user->id;
        $this->data['user'] = $user;
        $this->data['status'] = 'default';
        $this->data['table'] = 'users';
        return view('auth.passwords.update', $this->data);
    }

    public function generateUsername($school_id, $role)
    {
        $uniq_num_gen_obj = new UniqNumGen();
        return $uniq_num_gen_obj->generateUsername($school_id, $role);
    }

    public function updateUniqNumDb($school_id, $role)
    {
        $uniq_num_gen_obj = new UniqNumGen();
        $uniq_num_gen_obj->updateUniqNumDb($school_id, $role);
    }
    public function accessDenied()
    {
        return $this->render('errors.403');
    }

    public function moduleNotEnabled($module_slug)
    {
        return $this->render('errors.module_not_enabled', compact('module_slug'));
    }

    public function setAdminRole()
    {
        $staff_roles = StaffRole::with('staff.user')->where('role', 'admin')->get();
        foreach ($staff_roles as $staff_role) {
            if ($staff_role->staff) {

                $user = $staff_role->staff->user;
                if ($user) {
                    $user->syncRoles([1]); // admin is 1
                }
            }
        }
    }

    public function fetchNecessayParams()
    {
        $countries = Country::with('states.lgas')->orderBy('country_name')->get();
        $selected_country = $countries->where('country_name', 'Nigeria')->first();
        return  response()->json(compact('countries', 'selected_country'), 200);
    }

    // public function artisanCommand(Request $request)
    // {
    //     \Illuminate\Support\Facades\Artisan::call($request->command);
    // }
}
