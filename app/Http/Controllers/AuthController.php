<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Staff;
use Validator;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    protected $username;
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('guest')->except('logout');
        //  $this->username = $this->findUsername();
    }
    public function findUsername()
    {
        $login = request()->input('username');

        $user = User::where('phone1', $login)->first();

        if ($user) {
            $fieldType =  'phone1';

            request()->merge([$fieldType => $login]);
        } else {
            $fieldType = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

            request()->merge([$fieldType => $login]);
        }


        return $fieldType;
    }
    public function username()
    {
        return $this->username;
    }
    /**
     * Create user
     *
     * @param  [string] name
     * @param  [string] email
     * @param  [string] password
     * @param  [string] password_confirmation
     * @return [string] message
     */
    public function register(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'username' => 'required|string|unique:users',
            'phone' => 'required|string|unique:users',
            'email' => 'required|string|unique:users',
            'password' => 'required|string',
            'c_password' => 'required|same:password'
        ]);

        $user = new User([
            'first_name'  => $request->first_name,
            'last_name'  => $request->last_name,
            'username' => $request->username,
            'phone'  => $request->phone,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        if ($user->save()) {
            $tokenResult = $user->createToken('Personal Access Token');
            $token = $tokenResult->plainTextToken;

            return response()->json([
                'message' => 'Successfully created user!',
                'accessToken' => $token,
            ], 201);
        } else {
            return response()->json(['error' => 'Provide proper details']);
        }
    }
    /**
     * Login user and create token
     *
     * @param Request $request
     */

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required|string',
            'remember_me' => 'boolean'
        ]);

        /** @var User $user */
        $user = User::where('username', $request->username)
            ->orWhere('email', $request->username)
            ->orWhere('phone1', $request->username)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'message' => 'invalid credentials',
            ]);
        }

        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->plainTextToken;

        return response()->json([
            'user' => new UserResource($user),
            'tk' => $token,
        ]);
    }


    /**
     * Get the authenticated User
     *
     * @return [json] user object
     */
    public function user()
    {
        return new UserResource(Auth::user());

    }

    /**
     * Logout user (Revoke the token)
     *
     * @return [string] message
     */
    // public function logout(Request $request)
    // {
    //     $request->user()->tokens()->delete();

    //     return response()->json([
    //         'message' => 'Successfully logged out'
    //     ]);
    // }
    public function logout(Request $request)
    {
        //return $request;
        // $this->guard()->logout();

        // $request->session()->invalidate();
        // $request->user()->tokens()->delete();
        $request->user()->currentAccessToken()->delete();
        if (isset($request->school_id)) {
            $school_id = $request->school_id;
            $admin_role_id = 1;
            //$school = School::find($school_id);

            $staff = Staff::join('role_user', 'role_user.user_id', '=', 'staff.user_id')->where(['staff.school_id' => $school_id, 'role_user.role_id' => $admin_role_id])->first();


            $user = $staff->user;
            return $this->generateAuthorizationKey($user);
        }
        if (isset($request->user_id)) {

            $user = User::find($request->user_id);
            return $this->generateAuthorizationKey($user);
            // if (Auth::loginUsingId($user_id)) {
            //     // Authentication passed...
            //     return redirect()->intended('dashboard');
            // }
        }
        return response()->json([
            'message' => 'success'
        ]);
    }

    public function confirmRegistration($hash)
    {


        $confirm_hash = User::where(['confirm_hash' => $hash, 'is_confirmed' => '0'])->first();

        if ($confirm_hash) {        //hash is confirmed and valid

            $confirm_hash->is_confirmed = '1';
            $confirm_hash->save();
            $message = 'confirmed';
            return $message;
            //return view('auth.registration_confirmed', compact('message'));

        }
        return 'Invalid Confirmation';
    }
}