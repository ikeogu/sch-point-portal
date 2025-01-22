<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\IncomeAndExpense;
use App\Models\SalaryPaymentMonitor;
use App\Models\SalaryScale;
use App\Models\Staff;
use App\Models\StaffSalaryPayment;
use Illuminate\Http\Request;

class SalaryController extends Controller
{
    //
    public function salaryScale()
    {
        $school_id = $this->getSchool()->id;

        $salary_scales = SalaryScale::with(['staff' => function ($q) use ($school_id) {
            $q->where('school_id', $school_id);
        }, 'staff.user'])->where('school_id', $school_id)->get();
        return $this->render(compact('salary_scales'), 200);
    }
    public function storeSalaryScale(Request $request)
    {
        $school_id = $this->getSchool()->id;
        $name = $request->name;
        $salary = $request->salary;

        $salary_scale = SalaryScale::where(['school_id' => $school_id, 'name' => $name])->first();
        if (!$salary_scale) {
            $salary_scale = new SalaryScale();
            $salary_scale->school_id = $school_id;
            $salary_scale->name = $name;
            $salary_scale->salary = $salary;
            $salary_scale->save();
        }
        return response()->json([], 200);
    }
    public function updateSalaryScale(Request $request, SalaryScale $salary_scale)
    {
        $salary_scale->name = $request->name;
        $salary_scale->salary = $request->salary;
        $salary_scale->save();
        return response()->json([], 200);
    }

    // public function fetchSalaryScaleWithStaff(Request $request)
    // {
    //     $school_id = $this->getSchool()->id;
    //     $staff = SalaryScale::with('staff.user')->get();
    //     return response()->json(compact('staff'), 200);
    // }
    public function assignStaffSalaryScale(Request $request, SalaryScale $salary_scale)
    {
        $staff_ids = $request->staff_ids;
        foreach ($staff_ids as $staff_id) {
            $staff = Staff::find($staff_id);
            $staff->salary_scale_id = $salary_scale->id;
            $staff->save();
        }
        return response()->json([], 200);
    }
    public function prepareSalarySheet(Request $request)
    {
        $school_id = $this->getSchool()->id;

        // $staff_id = $this->getStaff()->id;


        $date_to_time = strtotime('now');

        if (isset($request->date)) { //$request->date is in form of YYYY-mm i.e (2010-02)

            $date_to_time = strtotime($request->date);
        }
        $pay_year = date('Y', $date_to_time);
        $pay_month = date('F', $date_to_time);

        $staff_level_setting_error = "";
        $salary_scales = SalaryScale::where('school_id', $school_id)->orderBy('salary')->get();

        if ($salary_scales->isNotEmpty()) {


            foreach ($salary_scales as $salary_scale) {
                $staff = $salary_scale->staff()->where('school_id', $school_id)->get();
                foreach ($staff as $each_staff) {

                    $appointment_date_to_time = strtotime($each_staff->appointment_date);

                    //we want to check whether the date selected is behind the date of appointment before we create salary payment
                    $salary_payment =  SalaryPaymentMonitor::where(['school_id' => $school_id, 'pay_month' => $pay_month, 'pay_year' => $pay_year, 'staff_id' => $each_staff->id])->first();
                    //create salarypayment if not exist
                    if (!$salary_payment) {

                        $staff_salary_payment = new SalaryPaymentMonitor();

                        $staff_salary_payment->school_id = $school_id;
                        $staff_salary_payment->staff_id = $each_staff->id;
                        $staff_salary_payment->salary_scale_id = $salary_scale->id;
                        $staff_salary_payment->salary = $salary_scale->salary;
                        $staff_salary_payment->amount_paid = 0;
                        $staff_salary_payment->deduction = 0;
                        $staff_salary_payment->addition = 0;
                        $staff_salary_payment->balance = $salary_scale->salary;
                        $staff_salary_payment->pay_month = date('F', strtotime('now')); //$pay_month;
                        $staff_salary_payment->pay_year = date('Y', strtotime('now')); //$pay_year;
                        // $staff_salary_payment->recorded_by = $this->getUser()->id;

                        $staff_salary_payment->save();
                    }
                }
            }
        }
        // $salary_payments = StaffSalaryPayment::where(['school_id' => $school_id, 'pay_month' => $pay_month, 'pay_year' => $pay_year])->get();
        // $data = compact('salary_payments');

        return $this->salaryPaymentsMonitor($request);
    }
    public function payStaffSalary(Request $request)
    {
        $school_id = $this->getSchool()->id;
        $recorded_by = $this->getUser()->id;


        $salary_payment_monitor_id = $request->salary_payment_monitor_id;
        $amount_paid = (int)($request->amount_paid);

        $payment_monitor = SalaryPaymentMonitor::find($salary_payment_monitor_id);
        $previous_payment = $payment_monitor->amount_paid;
        $salary = $payment_monitor->salary;
        if (($amount_paid + $previous_payment) > $salary) {
            return response()->json(['message' => 'Excess Payment'], 500);
        }
        $payment_monitor->amount_paid += $amount_paid;
        $payment_monitor->balance -= $amount_paid;
        $payment_monitor->save();

        $staff_salary_payment = new StaffSalaryPayment();
        $staff_salary_payment->school_id = $school_id;
        $staff_salary_payment->staff_id = $payment_monitor->staff_id;
        $staff_salary_payment->salary_payment_monitor_id = $salary_payment_monitor_id;
        $staff_salary_payment->amount_paid = $amount_paid;
        $staff_salary_payment->recorded_by = $recorded_by;

        // $date = $staff_salary_payment->pay_year . '-' . date('m', strtotime($staff_salary_payment->pay_month)); //the date for the current payment
        if ($staff_salary_payment->save()) {
            //lets also update the income_and_expenses as expenses since we are giving out money
            $staff = Staff::find($staff_salary_payment->staff_id);
            $staff_salary_payment->school_id = $school_id;
            $staff_salary_payment->sess_id = $this->getSession()->id;
            $staff_salary_payment->term_id = $this->getTerm()->id;
            $staff_salary_payment->purpose = 'Staff Salary for ' . $payment_monitor->pay_month . ', ' . $staff_salary_payment->pay_year;
            $staff_salary_payment->date = 'now';
            $staff_salary_payment->payer_recipient_id = $staff->user_id;
            $staff_salary_payment->amount_paid = $amount_paid; // + $addition;
            $staff_salary_payment->payer_recipient_role = 'staff';
            $staff_salary_payment->status = 'expenses';

            $income_expenses_obj = new IncomeAndExpense();
            $income_expenses_obj->addIncomeAndExpenses($staff_salary_payment);
            //expenses table populated
        }
        return response()->json([], 200);
    }
    public function salaryPaymentsMonitor(Request $request)
    {
        $school_id = $this->getSchool()->id;

        $date_to_time = strtotime('now');

        if (isset($request->date)) { //$request->date is in form of YYYY-mm i.e (2010-02)

            $date_to_time = strtotime($request->date);
        }
        $pay_year = date('Y', $date_to_time);
        $pay_month = date('F', $date_to_time);

        $salary_payments =  SalaryPaymentMonitor::with('staff.user', 'salaryScale', 'payments')->where(['school_id' => $school_id, 'pay_month' => $pay_month, 'pay_year' => $pay_year])->orderBy('amount_paid')->get();

        $data = compact('salary_payments');

        return $this->render($data);
    }
}
