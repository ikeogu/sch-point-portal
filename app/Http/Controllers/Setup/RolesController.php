<?php

namespace App\Http\Controllers\Setup;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\CurriculumLevelGroup;

class RolesController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = $this->getUser();
        if ($user->hasRole('super')) {
            $roles = Role::with('permissions')->where('school_id', NULL)->get();
        } else {

            $school = $this->getSchool();
            $roles = Role::with('permissions')->where('role_type', 'staff')
                ->where(function ($query) use ($school) {
                    $query->where('school_id', $school->id);
                    $query->orWhere('school_id', 0);
                })->get();
            foreach ($roles as $role) {
                $role->level_groups = NULL;
                if ($role->curriculum_level_group_ids !== NULL) {
                    $curriculum_level_group_ids = explode('~', $role->curriculum_level_group_ids);
                    $curriculum_level_groups = CurriculumLevelGroup::whereIn('id', $curriculum_level_group_ids)->get();

                    $role->level_groups = $curriculum_level_groups;
                }
            }
        }
        return $this->render(compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $user = $this->getUser();
        $name = strtolower(str_replace(' ', '-', $request->name));
        $level_groups = implode('~', $request->level_groups);
        if ($user->hasRole('super')) {
            $school_id = NULL;
            $role = Role::where('name', $name)->first();
        } else {

            $school = $this->getSchool();
            $school_id = $school->id;
            $role = Role::where('name', $name)
                ->where(function ($query) use ($school) {
                    $query->where('school_id', $school->id);
                    $query->orWhere('school_id', 0);
                })->first();
        }
        if (!$role) {
            $role = new Role();
            $role->name = $name;
            $role->school_id = $school_id;
            $role->display_name = ucwords(str_replace('-', ' ', $name));
            $role->description = $request->description;
            $role->curriculum_level_group_ids = $level_groups;
            $role->save();
        }
        return $this->index($request);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Role  $role
     * @return \Illuminate\Http\Response
     */
    public function show(Role $role)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Role  $role
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Role $role)
    {
        //
        $level_groups = implode('~', $request->level_groups);
        $name = strtolower(str_replace(' ', '-', $request->name));
        $role->name = $name;
        $role->display_name = ucwords(str_replace('-', ' ', $name));
        $role->description = $request->description;
        $role->curriculum_level_group_ids = $level_groups;
        $role->save();
        return $this->index($request);
    }

    public function assignRoles(Request $request)
    {
        $user = User::find($request->user_id);
        $user->syncRoles($request->roles);
        $user->flushCache();
        $roles = $user->roles()->with('permissions')->get();
        $permissions = [];
        foreach ($roles as $role) {
            $permissions = array_merge($permissions, $role->permissions->toArray());
        }
        return response()->json(compact('roles', 'permissions'), 200);
    }
    // public function removeAssignedRole(Request $request)
    // {
    //     $user = User::find($request->user_id);
    //     $user->detachRole($request->roles);
    // }
}
