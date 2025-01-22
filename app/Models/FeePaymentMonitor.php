<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeePaymentMonitor extends Model
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function school()
    {
        return $this->belongsTo(School::class);
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function session()
    {
        return $this->belongsTo(SSession::class, 'sess_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function term()
    {
        return $this->belongsTo(Term::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function level()
    {
        return $this->belongsTo(Level::class);
    }

    public function schoolFeePayments()
    {
        return $this->hasMany(SchoolFeePayment::class);
    }

    public function paymentMonitorData($school_id, $term_id, $sess_id, $student, $school_fee)
    {
        $payment_data['school_id'] = $school_id;
        $payment_data['student'] = $student; //$this->getStudent();

        $payment_data['school_fee'] = $school_fee->fee;


        //$payment_data['accommodation_fee_id'] = NULL;
        $payment_data['discount'] = 0;
        $payment_data['amount_due'] = 0; //$amount_due;

        $payment_data['amount_paid'] = 0;
        $payment_data['balance'] = 0;
        $payment_data['sess_id'] = $sess_id; //$this->getSession()->id;
        $payment_data['term_id'] = $term_id; //$this->getSemester()->id;


        return $payment_data;
    }
    /**
     *This creates a new entry for payment monitor if entry does not exit
     * @param array $data. This contains elements to be entered in the db
     * @return boolean True if successful and False if failed
     */
    public function createSchoolFeesPaymentMonitor($data, $level_id, $applicable_fee_ids_array)
    {

        // $amount_paid = $data->amount_paid;

        $school_id = $data->school_id;
        $sess_id = $data->sess_id;
        //$semester_id = $data->semester_id;
        $term_id = $data->term_id;

        $student_id = $data->student_id;
        $discount = $data->discount;
        $total_fee = $data->total_fee;
        $applicable_fee_ids = $data->applicable_fee_ids;
        // $other_fees = $data->other_fees;
        // $total_fee = $data->total_fee; // + $debt;

        // $amount_due = $total_fee - $discount;


        // $balance = $amount_due - $amount_paid;


        $payment_monitor = FeePaymentMonitor::where(['school_id' => $school_id, 'student_id' => $student_id, 'sess_id' => $sess_id, 'level_id' => $level_id, 'term_id' => $term_id])->first();

        if (!$payment_monitor) {
            //update
            $payment_monitor = new FeePaymentMonitor();


            $payment_monitor->school_id = $school_id;
            $payment_monitor->student_id = $student_id;

            $payment_monitor->sess_id = $sess_id;
            $payment_monitor->term_id = $term_id;
            $payment_monitor->level_id = $level_id;
            $payment_monitor->non_applicable_fee_ids = '';
        }
        $payment_monitor->total_fee = $total_fee;
        $payment_monitor->applicable_fee_ids = $this->getApplicableFeeIds($payment_monitor->non_applicable_fee_ids, $applicable_fee_ids_array);
        // $payment_monitor->balance = $balance;
        $payment_monitor->discount = $discount;


        $payment_monitor->save();
        return $payment_monitor;
    }
    private function getApplicableFeeIds($non_applicable_fee_ids, $fee_ids_array)
    {
        $applicable_fee_ids = [];
        $non_applicable_fee_ids_array = explode('~', $non_applicable_fee_ids);
        foreach ($fee_ids_array as $id) {
            if (!in_array($id, $non_applicable_fee_ids_array)) {
                $applicable_fee_ids[] = $id;
            }
        }
        return implode('~', $applicable_fee_ids);
    }
    public function addOtherFeePaymentMonitor($data, $level_id)
    {

        // $amount_paid = $data->amount_paid;

        $school_id = $data->school_id;
        $sess_id = $data->sess_id;
        //$semester_id = $data->semester_id;
        $term_id = $data->term_id;

        $student_id = $data->student_id;
        $other_fees = $data->other_fees;

        // $balance = $amount_due - $amount_paid;


        $payment_monitor = FeePaymentMonitor::where(['school_id' => $school_id, 'student_id' => $student_id, 'sess_id' => $sess_id, 'level_id' => $level_id, 'term_id' => $term_id])->first();

        if ($payment_monitor) {
            //update
            $payment_monitor->other_fees = $other_fees;
            $payment_monitor->save();
            return $payment_monitor;
        }
    }

    /**
     *This updates the payment monitor table for all school fees paid
     *@param Collection $paid_fee this hold value of a successful payment
     *@return void
     */
    public function updatePaymentMonitor($paid_fee)
    {
        $amount_paid = $paid_fee->amount;

        $this->amount_paid +=  $amount_paid;

        $this->save();
    }
}
