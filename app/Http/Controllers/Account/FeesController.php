<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\FeePaymentMonitor;
use App\Models\FeePurpose;
use App\Models\Level;
use App\Models\OtherFee;
use App\Models\SchoolFee;
use DB;
use Illuminate\Http\Request;

class FeesController extends Controller
{
  public function savePaystackKey(Request $request)
  {
    $school = $this->getSchool();
    $school->paystack_key = $request->paystack_key;
    $school->save();
    return response()->json([], 204);
  }
  public function schoolFeeSettings(Request $request)
  {
    $school_id = $this->getSchool()->id;
    $term_id = $request->term_id;
    $school_fees = SchoolFee::with('level', 'term')
      ->where(['school_id' => $school_id, 'term_id' => $term_id])
      ->orderBy('id', 'DESC')
      ->get();
    return $this->render(compact('school_fees'), 200);
  }
  public function viewStudentFeeDetails(Request $request, FeePaymentMonitor $payment_monitor)
  {
    $applicable_fee_ids = $payment_monitor->applicable_fee_ids;
    $non_applicable_fee_ids = $payment_monitor->non_applicable_fee_ids;
    $applicable_fee_ids_array = explode('~', $applicable_fee_ids);
    $non_applicable_fee_ids_array = explode('~', $non_applicable_fee_ids);
    $applicable_fees = SchoolFee::with('level')->whereIn('id', $applicable_fee_ids_array)->get();
    $non_applicable_fees = SchoolFee::with('level')->whereIn('id', $non_applicable_fee_ids_array)->get();

    return $this->render(compact('applicable_fees', 'non_applicable_fees'), 200);
  }
  public function feePurposes()
  {

    $fee_purposes = FeePurpose::get();
    return $this->render(compact('fee_purposes'), 200);
  }
  public function storeFeeSettings(Request $request)
  {
    $school_id = $this->getSchool()->id;
    $term_id = $request->term_id;
    $level_ids = $request->level_ids;
    $amount = $request->amount;
    $purpose = $request->purpose;
    $vat = $request->vat;

    foreach ($level_ids as $level_id) :
      $school_fee = SchoolFee::where(['term_id' => $term_id, 'level_id' => $level_id, 'purpose' => $purpose])->first();

      if (!$school_fee) {
        $school_fee = new SchoolFee();
      }
      $school_fee->school_id = $school_id;
      $school_fee->term_id = $term_id;
      $school_fee->level_id = $level_id;
      $school_fee->amount = $amount;
      $school_fee->purpose = $purpose;
      $school_fee->vat = $vat;
      $school_fee->save();
    endforeach;
    // add a new fee purpose if it does not exist
    $fee_purpose = FeePurpose::where('name', 'LIKE', '%' . $purpose . '%')->first();
    if (!$fee_purpose) {
      $fee_purpose = new FeePurpose();
      $fee_purpose->name = $purpose;
      $fee_purpose->save();
    }


    return response()->json([], 200);
  }

  public function updateFeeSetting(Request $request, SchoolFee $school_fee)
  {
    $amount = $request->amount;
    $vat = $request->vat;
    $school_fee->amount = $amount;
    $school_fee->vat = $vat;
    $school_fee->save();

    return response()->json([], 200);
  }
  public function changeFeeStatus(Request $request, SchoolFee $school_fee)
  {
    $school_fee->is_active =  $request->is_active;
    $school_fee->save();

    return response()->json([], 200);
  }
  public function applyStudentsSchoolFees(Request $request)
  {
    $payment_obj = new FeePaymentMonitor();
    $school_id = $this->getSchool()->id;
    $sess_id = $this->getSession()->id;
    $term_id = $request->term_id;
    $level_ids = json_decode(json_encode($request->level_ids));
    foreach ($level_ids as $level_id) {

      $level = Level::with(['studentsInClass' => function ($q) use ($sess_id, $school_id) {
        $q->where([
          'sess_id' => $sess_id,
          'students_in_classes.school_id' => $school_id
        ]);
      }])->find($level_id);

      $school_fees = SchoolFee::with(['level.studentsInClass' => function ($q) use ($sess_id, $school_id) {
        $q->where([
          'sess_id' => $sess_id,
          'students_in_classes.school_id' => $school_id
        ]);
      }])
        ->groupBy('level_id')
        ->where('level_id', $level_id)
        ->where(['school_id' => $school_id, 'term_id' => $term_id])
        ->where('is_active', 1)
        ->select('*', \DB::raw('SUM(amount + vat) as total_amount'))
        ->get();

      $fee_ids = SchoolFee::where('level_id', $level_id)
        ->where(['school_id' => $school_id, 'term_id' => $term_id])
        ->where('is_active', 1)
        ->pluck('id');

      $applicable_fee_ids_array = $fee_ids->toArray();
      if ($school_fees->isNotEmpty()) {
        foreach ($school_fees as $school_fee) {
          $level_id = $school_fee->level_id;
          $total_fee = $school_fee->total_amount;

          $students_in_class = $level->studentsInClass;
          if (!empty($students_in_class)) {

            foreach ($students_in_class as $student_in_class) {
              $request->student_id = $student_in_class->student_id;
              $request->discount = 0;
              $request->total_fee = $total_fee;
              $request->school_id = $school_id;
              $request->sess_id = $sess_id;
              $request->term_id = $term_id;
              $payment_obj->createSchoolFeesPaymentMonitor($request, $level_id, $applicable_fee_ids_array);
            }
          }
        }
      }
    }
    return response()->json([], 200);
  }
  public function applyStudentsSchoolFees2(Request $request)
  {
    $payment_obj = new FeePaymentMonitor();
    $school_id = $this->getSchool()->id;
    $sess_id = $this->getSession()->id;

    $level_ids = json_decode(json_encode($request->level_ids));
    // $type = $request->type;
    $term_id = $request->term_id;

    // we want to pick the fee id so that we can make them applicable
    $fee_ids = SchoolFee::whereIn('level_id', $level_ids)
      ->where(['school_id' => $school_id, 'term_id' => $term_id])
      ->where('is_active', 1)
      ->pluck('id');

    $applicable_fee_ids_array = $fee_ids->toArray();

    $school_fees = SchoolFee::with(['level.studentsInClass' => function ($q) use ($sess_id, $school_id) {
      $q->where([
        'sess_id' => $sess_id,
        'students_in_classes.school_id' => $school_id
      ]);
    }])
      ->groupBy('level_id')
      ->whereIn('level_id', $level_ids)
      ->where(['school_id' => $school_id, 'term_id' => $term_id])
      ->where('is_active', 1)
      ->select('*', \DB::raw('SUM(amount + vat) as total_amount'))
      ->get();
    foreach ($school_fees as $school_fee) {
      $level_id = $school_fee->level_id;
      $total_fee = $school_fee->total_amount;
      $level = Level::with(['studentsInClass' => function ($q) use ($sess_id, $school_id) {
        $q->where([
          'sess_id' => $sess_id,
          'students_in_classes.school_id' => $school_id
        ]);
      }])->find($level_id);

      $students_in_class = $level->studentsInClass;
      if (!empty($students_in_class)) {

        foreach ($students_in_class as $student_in_class) {
          $request->student_id = $student_in_class->student_id;
          $request->discount = 0;
          $request->total_fee = $total_fee;
          $request->school_id = $school_id;
          $request->sess_id = $sess_id;
          $request->term_id = $term_id;
          $payment_obj->createSchoolFeesPaymentMonitor($request, $level_id, $applicable_fee_ids_array);
        }
      }
    }

    return response()->json([], 200);
  }
  public function setPayableAndNonPayableFee(Request $request, FeePaymentMonitor $payment_monitor)
  {
    $fee_id = $request->fee_id;
    $total = $request->total;
    $type = $request->type;

    $applicable_fee_ids = $payment_monitor->applicable_fee_ids;
    $non_applicable_fee_ids = $payment_monitor->non_applicable_fee_ids;

    if ($type === 'add') {
      $non_applicable_fee_ids = deleteSingleElementFromString($non_applicable_fee_ids, $fee_id);
      $applicable_fee_ids = addSingleElementToString($applicable_fee_ids, $fee_id);
      $payment_monitor->total_fee += $total;
    }
    if ($type === 'remove') {
      $applicable_fee_ids = deleteSingleElementFromString($applicable_fee_ids, $fee_id);
      $non_applicable_fee_ids = addSingleElementToString($non_applicable_fee_ids, $fee_id);
      $payment_monitor->total_fee -= $total;
    }

    $payment_monitor->applicable_fee_ids = $applicable_fee_ids;
    $payment_monitor->non_applicable_fee_ids = $non_applicable_fee_ids;
    $payment_monitor->save();

    // we return the full details again
    return $this->viewStudentFeeDetails($request, $payment_monitor);
  }



  // public function otherFeeSettings(Request $request)
  // {
  //     $school_id = $this->getSchool()->id;
  //     $term_id = $request->term_id;
  //     $other_fees = OtherFee::with('level')
  //     ->where(['school_id' => $school_id, 'term_id' => $term_id])
  //         ->orderBy('id', 'DESC')
  //         ->get();
  //     return $this->render(compact('other_fees'), 200);
  // }

  // public function storeOtherFeeSettings(Request $request)
  // {
  //     $school_id = $this->getSchool()->id;
  //     $level_ids = $request->level_ids;
  //     // $is_active = $request->is_active;
  //     $amount = $request->amount;
  //     $term_id = $request->term_id;
  //     $purpose = $request->purpose;
  //     $recurrence = $request->recurrence;
  //     $vat = $request->vat;

  //     foreach ($level_ids as $level_id) {
  //         $other_fee = OtherFee::where(['level_id' => $level_id, 'purpose' => $purpose])->first();

  //         if (!$other_fee) {
  //             $other_fee = new OtherFee();
  //         }
  //         $other_fee->school_id = $school_id;
  //         $other_fee->level_id = $level_id;
  //         $other_fee->term_id = $term_id;
  //         $other_fee->purpose = $purpose;
  //         $other_fee->amount = $amount;
  //         $other_fee->vat = $vat;
  //         // $other_fee->is_active = $is_active;
  //         $other_fee->recurrence = $recurrence;
  //         $other_fee->save();
  //     }
  //     return response()->json([], 200);
  // }

  // public function updateOtherFeeSetting(Request $request, OtherFee $other_fee)
  // {
  //     $other_fee->amount = $request->amount;
  //     $other_fee->vat = $request->vat;
  //     // $other_fee->purpose = $request->purpose;
  //     $other_fee->recurrence =  $request->recurrence;
  //     $other_fee->save();

  //     return response()->json([], 200);
  // }

}
