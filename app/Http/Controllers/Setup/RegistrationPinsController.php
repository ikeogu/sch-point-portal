<?php

namespace App\Http\Controllers\Setup;

use App\Models\RegistrationPin;
use App\Models\School;
use Illuminate\Http\Request;
use Laracasts\Flash\Flash;
use App\Http\Controllers\Controller;

class RegistrationPinsController extends Controller
{
    public function confirmPin(Request $request)
    {
        //
        $pin = $request->pin;
        $fetched_pin = RegistrationPin::with('school')->where('pin', $pin)->where('status', 'given')->first();

        return response()->json(compact('fetched_pin'));
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */


    public function studentsPins()
    {
        //
        $school_id = $this->getSchool()->id;
        $student_reg_pins = RegistrationPin::where('school_id', $this->getSchool()->id)->where('pin_type', 'student')->orderBy('status')->get();

        return response()->json(compact('student_reg_pins'));
    }
    public function staffPins()
    {
        //
        $school_id = $this->getSchool()->id;
        $staff_reg_pins = RegistrationPin::where('school_id', $this->getSchool()->id)->where('pin_type', 'staff')->orderBy('status')->get();

        return response()->json(compact('staff_reg_pins'));
    }

    // public function managePins()
    // {
    //     //
    //     $reg_pins = RegistrationPin::get();
    //     return $this->render('pins.manage', compact('reg_pins'));
    // }

    public function generatePin($type)
    {
        $schools = School::get();
        $school_array = [];
        if ($schools != '[]') {

            foreach ($schools as $school) {
                $school_array[$school->id] = $school->name;
            }
        }
        return $this->render('pins.generate', compact('type', 'school_array'));
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function changeStatus(Request $request, RegistrationPin $registrationPin)
    {
        //
        $registrationPin->status = $request->status;
        $registrationPin->save();
        return response()->json([], 204);
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
        $quantity = $request->quantity;
        $type = $request->pin_type;
        $is_general = 1;
        $status = 'given';
        if ($type === 'student') {
            $is_general = 0;
            $status = 'unused';
        }
        if ($request->pin_type === 'general_student') {
            $type = 'student';
        }
        // $school_id = $request->school_id;
        $school_id = $this->getSchool()->id;

        for ($i = 1; $i <= $quantity; $i++) {

            $pin = randomCode();
            // make sure we don't have a duplicate pin
            $check_pin = RegistrationPin::where('pin', $pin)->first();

            if (!$check_pin) {
                $reg_pin = RegistrationPin::where(['school_id' => $school_id, 'is_general' => 1, 'pin_type' => $type])->first();
                if (!$reg_pin) {
                    $reg_pin = new RegistrationPin();

                    $reg_pin->school_id = $school_id;
                    $reg_pin->pin_type = $type;
                    $reg_pin->is_general = $is_general;
                    $reg_pin->status = $status; //unused, used or given
                }
                $reg_pin->pin = $pin;
                $reg_pin->save();
            }
        }
        return response()->json([], 204);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\RegistrationPin  $registrationPin
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $pins = $request->pins;
        foreach ($pins as $pin) {
            $registrationPin = RegistrationPin::find($pin);

            $registrationPin->delete();
        }
        return response()->json([], 204);
    }
}
