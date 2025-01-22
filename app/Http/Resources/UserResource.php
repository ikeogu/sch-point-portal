<?php

namespace App\Http\Resources;

use App\Models\SSession;
use App\Models\Term;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        /**
         * @var \App\Models\User $currentUser
         */
        $currentUser = Auth::user();
        $can_edit = false;

        if ($this->id == $currentUser?->id || $currentUser?->hasRole('super')) {
            $can_edit = true;
        }
        $user_role = [$this->role];
        $roles = array_map(
            function ($role) {
                return $role['name'];
            },
            $this->roles->toArray()
        );
        // $permissions = array_map(
        //     function ($permission) {
        //         return $permission['name'];
        //     },
        //     $this->allPermissions()->toArray()
        // );
        $rights = array_merge($roles, $user_role);
        $school = '';

        $my_wards_ids = [];
        $suspended_for_nonpayment = 0;
        if ($this->student) {
            $school  = $this->student->school()->with(['package.packageModules.module', 'currentTerm', 'currentSession'])->first();
            $suspended_for_nonpayment = $school->suspended_for_nonpayment;
            if ($this->student->studentship_status != 'active') {
                $suspended_for_nonpayment = 1;
            }
        }
        if ($this->guardian) {
            $wards = $this->guardian->guardianStudents;
            $school  = $this->guardian->school()->with(['package.packageModules.module', 'currentTerm', 'currentSession'])->first();
            $active = 0;
            $suspended_for_nonpayment = $school->suspended_for_nonpayment;
            foreach ($wards as $ward) {
                $student = $ward->student;
                if ($student->studentship_status === 'active') {
                    $active++;
                }
                $my_wards_ids[] = $ward->student_id;
            }

            if ($active == 0) {
                $suspended_for_nonpayment = 1;
            }
        }
        if ($this->staff) {
            $school  = $this->staff->school()->with(['package.packageModules.module', 'currentTerm', 'currentSession'])->first();
            $suspended_for_nonpayment = $school->suspended_for_nonpayment;
        }
        $modules = [];
        $dir_size = 0;
        $percentage_used_space = 0;
        $used_space = '0 Byte';
        $system_set_session = SSession::where('is_active', '1')->orderBy('id', 'DESC')->first();
        $system_set_term = Term::where('is_active', '1')->first();
        $whatsapp_no = '2347044449412';
        if ($school != '') {
            $module_packages = $school->package->packageModules;
            foreach ($module_packages as $module_package) {

                $modules[] = $module_package->module->slug;
            }
            if ($school->suspended_for_nonpayment == 1) {
                $suspended_for_nonpayment = 1;
            }
            $dir_size_in_byte = folderSize('storage/schools/' . $school->folder_key . '/');
            $dir_size = byteToGB($dir_size_in_byte);
            $percentage_used_space = percentageDirUsage($dir_size, $school->disk_space);
            $used_space = folderSizeFilter($dir_size_in_byte);
            $whatsapp_no = $school->whatsapp_no;
        }
        return [
            'id' => $this->id,
            'name' => $this->first_name . ' ' . $this->last_name,
            'first_name' => $this->first_name,
            'last_name' =>  $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone1 . ' | ' . $this->phone2,
            'username' => $this->username,
            'gender' => $this->gender,
            'address' =>  $this->address,
            'student' => $this->student,
            'guardian' => $this->guardian,
            'my_wards_ids' => $my_wards_ids,
            'staff' => $this->staff,
            'school' => $school,
            'system_set_session' => $system_set_session,
            'system_set_term' => $system_set_term,
            'suspended_for_nonpayment' => $suspended_for_nonpayment,
            'password_status' => $this->password_status,
            'notifications' => [],
            'dir_size' => $dir_size,
            'percentage_used_space' => $percentage_used_space,
            'used_space' => $used_space,
            // 'activity_logs' => $this->notifications()->orderBy('created_at', 'DESC')->get(),
            'roles' => $rights,
            'modules' => $modules,
            // 'role' => 'admin',
            'permissions' => array_map(
                function ($permission) {
                    return $permission['name'];
                },
                $this->allPermissions()->toArray()
            ),
            'avatar' => '/' . $this->photo, //'https://i.pravatar.cc',
            'photo' => '/' . $this->photo,
            'can_edit' => $can_edit,
            'whatsapp_no' => $whatsapp_no,
        ];
    }
}