<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class PartnersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $staff = Partner::with(['user.roles', 'user.partnerSchools.school.package'])->get();
        foreach ($staff as $each_staff) {
            $each_staff->user->permissions = $each_staff->user->allPermissions();
        }

        return $this->render(compact('staff'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Partner $partner,  User $user)
    {
        try {
            $saved_user = $user->saveUserAsPartner($request);
            $request->user_id = $saved_user->id;
            // asssign the roles to the user
            $saved_user->syncRoles([$request->role]);
            $partner = Partner::where('user_id', $saved_user->id)->first();
            if (!$partner) {
                $partner = new Partner();
            }
            $partner->user_id = $request->user_id;
            $partner->save();

            //$action = "Registered " . $request->first_name . " " . $request->last_name . " as new partner";
            // $this->auditTrailEvent($request, $action);
            //$new_user = User::find($request->student_user_id);
            //$all_staff = User::where('role', 'staff')->get();
            //$user->notify(new NewRegistration($user));
            //Notification::send($all_staff, new NewRegistration($new_user));
            return 'Successful';

            // Flash::success('Staff information added successfully');
        } catch (ModelNotFoundException $ex) {
            // Flash::error('Error: ' . $ex->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Partner  $partner
     * @return \Illuminate\Http\Response
     */
    public function show(Partner $partner)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Partner  $partner
     * @return \Illuminate\Http\Response
     */
    public function edit(Partner $partner)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Partner  $partner
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Partner $partner)
    {
        //

        try {

            $partner_user = User::find($request->id);
            $partner_user->first_name = $request->first_name;
            $partner_user->last_name = $request->last_name;
            $partner_user->email = $request->email;
            $partner_user->address = $request->address;
            $partner_user->phone1 = $request->phone1;
            $partner_user->phone2 = $request->phone2;
            $partner_user->gender = $request->gender;
            $partner_user->save();

            return response()->json(compact('partner_user'), 200);
        } catch (ModelNotFoundException $ex) {
            return response()->json(compact('ex'), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Partner  $partner
     * @return \Illuminate\Http\Response
     */
    public function destroy(Partner $partner)
    {
        //
    }
}
