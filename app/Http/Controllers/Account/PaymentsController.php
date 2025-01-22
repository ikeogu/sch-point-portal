<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Level;
use App\Models\User;
use App\Models\Staff;
use App\Models\SSession;
use App\Models\Term;
use App\Models\FeePaymentMonitor;
use App\Models\SchoolFeePayment;
use App\Models\OtherFeePayment;
use App\Models\OtherFee;
use App\Models\SchoolFee;
use App\Models\StaffLevel;
use App\Models\StaffSalaryPayment;
use App\Models\IncomeAndExpense;

use Illuminate\Http\Request;

class PaymentsController extends Controller
{
    public function paymentsMonitorTable(Request $request)
    {
        $school_id = $this->getSchool()->id;
        $sess_id = $request->sess_id;
        $term_id = $request->term_id;
        $level_id = $request->level_id;
        $condition = [
            'school_id' => $school_id,
            'sess_id' => $sess_id,
            'term_id' => $term_id,
            'level_id' => $level_id
        ];
        if ($level_id === 'all') {
            $condition = [
                'school_id' => $school_id,
                'sess_id' => $sess_id,
                'term_id' => $term_id
            ];
        }
        $payment_monitors = FeePaymentMonitor::with(
            'student.user',
            'level',
            'schoolFeePayments',
            'session',
            'term'
        )
            ->where($condition)
            ->orderBy('amount_paid')
            ->get();

        return $this->render(compact('payment_monitors'), 200);
    }
    public function studentPaymentTable(Request $request)
    {
        $school_id = $this->getSchool()->id;
        if (isset($request->student_id) && $request->student_id != '') {
            $student_id = $request->student_id;
        } else {

            $student_id = $this->getStudent()->id;
        }
        $sess_id = $request->sess_id;
        $payment_monitors = FeePaymentMonitor::with(
            'student.user',
            'level',
            'schoolFeePayments',
            'session',
            'term'
        )
            ->where([
                'school_id' => $school_id,
                'sess_id' => $sess_id,
                'student_id' => $student_id
            ])
            ->get();


        return $this->render(compact('payment_monitors'), 200);
    }
    public function otherFeePayments(Request $request)
    {
        $school_id = $this->getSchool()->id;
        $sess_id = $this->getSession()->id;
        $term_id = $this->getTerm()->id;
        $level_id = $request->level_id;
        $other_fee_payments = OtherFeePayment::with(
            'student.user',
            'level',
            'otherFee',
            'session',
            'term'
        )
            ->where([
                'school_id' => $school_id,
                'sess_id' => $sess_id,
                'term_id' => $term_id,
                'level_id' => $level_id
            ])
            ->get();

        return response()->json(compact('other_fee_payments'), 200);
    }
    // public function feeCollectionTable(Request $request)
    // {
    //     $school_id = $this->getSchool()->id;
    //     $sess_id = $this->getSession()->id;
    //     $term_id = $this->getTerm()->id;

    //     $staff_id = $this->getStaff()->id;
    //     if (isset($request->sess_id, $request->term_id)) {
    //         $sess_id = $request->sess_id;
    //         $term_id = $request->term_id;
    //     }
    //     $selected_session = SSession::find($sess_id);
    //     $selected_term = Term::find($term_id);

    //     $all_sessions = SSession::where('id', '<=', $this->getSession()->id)->orderBY('id', 'DESC')->get();
    //     foreach ($all_sessions as $session) {
    //         $session_array[$session->id] = $session->name;
    //     }

    //     $all_terms = Term::orderBY('id')->get();
    //     foreach ($all_terms as $term) {
    //         $term_array[$term->id] = $term->name;
    //     }




    //     $students = Student::where(['school_id' => $school_id, 'is_active' => '1'])->where('admission_sess_id', '<=', $sess_id)->orderBY('registration_no')->get();

    //     $stud_obj = new Student();
    //     $payment_monitor_obj = new FeePaymentMonitor();
    //     //fetch student's complete information
    //     $students = $stud_obj->allStudentInformation($students, $sess_id, $term_id, $school_id);




    //     $fee_setting_error = [];
    //     if ($students != '[]') {


    //         foreach ($students as $student) {

    //             $school_fee = SchoolFee::where(['school_id' => $school_id, 'level_id' => $student->current_level, 'term_id' => $term_id])->first();
    //             if ($school_fee) {
    //                 //try to creat payment monitor for the active term and not the selected term for each students
    //                 $data = $payment_monitor_obj->paymentMonitorData($school_id, $this->getTerm()->id, $this->getSession()->id, $student, $school_fee);

    //                 $payment_monitor_obj->createPaymentMonitor($data, $school_fee->level_id);
    //             } else {
    //                 $fee_setting_error[] = Level::find($student->current_level)->level;
    //             }


    //             $student->fee_payment_monitor =  $student->feePaymentMonitor()->where(['sess_id' => $sess_id, 'term_id' => $term_id])->first();

    //             $fee_payment_monitor = $payment_monitor_obj->updatePaymentMonitor($student->fee_payment_monitor);

    //             $student->fee_payment_monitor = $fee_payment_monitor; //the updated payment monitor


    //         }
    //     }
    //     sort($fee_setting_error); //arrange aphabetically
    //     $make_settings = implode(',', array_unique($fee_setting_error));

    //     $data = compact('students', 'selected_session', 'selected_term', 'term_array', 'session_array', 'make_settings',  'staff_id');

    //     return $this->render('account::schoolfees.fee_collection_table', $data);
    // }
    public function payViaCash(Request $request)
    {
        $logged_by = $this->getUser()->id;
        $school_id = $this->getSchool()->id;
        $receipt_no = $request->receipt_no;

        if (SchoolFeePayment::where(['school_id' => $school_id, 'receipt_no' => $receipt_no])->first()) {
            //receipt number exist already
            return response()->json(['message' => 'DUPLICATE RECEIPT NO.'], 200);
        }
        $school_fee_payment = new SchoolFeePayment();

        $school_fee_payment->school_id = $school_id;
        $school_fee_payment->amount = $request->amount;
        $school_fee_payment->receipt_no = $receipt_no;
        $school_fee_payment->reference = $receipt_no;
        $school_fee_payment->mode = 'Cash';
        $school_fee_payment->pay_date = date('Y-m-d', strtotime('now')); // $request->pay_date;
        $school_fee_payment->student_id = $request->student_id;
        $school_fee_payment->fee_payment_monitor_id = $request->fee_payment_monitor_id;
        $school_fee_payment->logged_by = $logged_by;
        $school_fee_payment->save();
        return response()->json([], 200);
    }

    public function payViaCard(Request $request)
    {
        $logged_by = $this->getUser()->id;
        $school_id = $this->getSchool()->id;
        $transaction = $request->transaction;
        $reference = $request->reference;
        $status = $request->status;
        $message = $request->message;

        if (SchoolFeePayment::where(['school_id' => $school_id, 'receipt_no' => $transaction])->first()) {
            //receipt number exist already
            return response()->json(['message' => 'DUPLICATE RECEIPT NO.'], 200);
        }
        $school_fee_payment = new SchoolFeePayment();

        $school_fee_payment->school_id = $school_id;
        $school_fee_payment->amount = $request->amount;
        $school_fee_payment->receipt_no = $transaction;
        $school_fee_payment->reference = $reference;
        $school_fee_payment->mode = 'Card';
        $school_fee_payment->status = $status;
        $school_fee_payment->message = $message;
        $school_fee_payment->pay_date = date('Y-m-d', strtotime('now')); // $request->pay_date;
        $school_fee_payment->student_id = $request->student_id;
        $school_fee_payment->fee_payment_monitor_id = $request->fee_payment_monitor_id;
        $school_fee_payment->logged_by = $logged_by;
        $school_fee_payment->save();
        if ($message == 'Approved' && $status == 'success') {
            $this->approveFeePayment($request, $school_fee_payment);
        }
        return response()->json([], 200);
    }

    public function approveFeePayment(Request $request, SchoolFeePayment $school_fee_payment)
    {
        // $approved_by = $this->getUser()->id;
        // $school_fee_payment->approved_by = $approved_by;
        $school_fee_payment->remitted = 1;
        $school_fee_payment->status = 'success';
        $school_fee_payment->message = 'Approved';
        if ($school_fee_payment->save()) {
            // we need to update the fee payment monitor table
            $payment_monitor = FeePaymentMonitor::find($school_fee_payment->fee_payment_monitor_id);
            $payment_monitor->updatePaymentMonitor($school_fee_payment);

            //lets also update the income_and_expenses table and save as income since we are receiving money
            $term = Term::find($payment_monitor->term_id)->name;
            $session = SSession::find($payment_monitor->sess_id)->name;
            $student = Student::find($school_fee_payment->student_id);

            $school_fee_payment->school_id = $school_fee_payment->school_id;
            $school_fee_payment->sess_id = $payment_monitor->sess_id;
            $school_fee_payment->term_id = $payment_monitor->term_id;

            $school_fee_payment->amount_paid = $school_fee_payment->amount;
            $school_fee_payment->purpose = 'Fee Payment for ' . $term . ' Term, ' . $session . ' Session';
            $school_fee_payment->date = $school_fee_payment->pay_date;
            $school_fee_payment->payer_recipient_id = $student->user_id;
            $school_fee_payment->payer_recipient_role = 'student';
            $school_fee_payment->status = 'income';

            $income_expenses_obj = new IncomeAndExpense();
            $income_expenses_obj->addIncomeAndExpenses($school_fee_payment);

            //income_and_expenses table populated

            // Flash::success('PAYMENT MADE SUCCESSFULLY');
            return response()->json([], 200);
        }
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function manageStaffWages(Request $request)
    {
        $date = date('Y-m', strtotime('now'));

        if (isset($request->date)) { //$request->date is in form of YYYY-mm i.e (2010-02)

            $date = $request->date;
        }
        return $this->render('account::staff_wages.manage_staff_wages', compact('date'));
    }
    public function staffPaymentTable(Request $request)
    {
        $school_id = $this->getSchool()->id;

        $staff_id = $this->getStaff()->id;


        $date_to_time = strtotime('now');

        if (isset($request->date)) { //$request->date is in form of YYYY-mm i.e (2010-02)

            $date_to_time = strtotime($request->date);
        }
        $pay_year = date('Y', $date_to_time);
        $pay_month = date('F', $date_to_time);

        $staff_level_setting_error = "";
        $staff_levels = StaffLevel::where('school_id', $school_id)->orderBy('staff_level', 'ASC')->get();

        $level_array = ['' => 'Select Level'];
        if ($staff_levels->count() > 0) {
            foreach ($staff_levels as $level) {
                $level_array[$level->id] = $level->staff_level;
            }
        } else {
            $staff_level_setting_error = "Set Level";
        }

        $staff = Staff::where(['school_id' => $school_id, 'is_active' => '1'])->get();


        if ($staff != '[]') {


            foreach ($staff as $each_staff) {
                $appointment_date_to_time = strtotime($each_staff->appointment_date);

                //we want to check whether the date selected is behind the date of appointment before we create salary payment
                $each_staff->staffSalaryPayment =  $each_staff->staffSalaryPayment()->where(['school_id' => $school_id, 'pay_month' => $pay_month, 'pay_year' => $pay_year])->first();
                //return $date_to_time.' '.$appointment_date_to_time;
                if ($date_to_time >= $appointment_date_to_time) {
                    //create salarypayment if not exist
                    if (!$each_staff->staffSalaryPayment && $each_staff->staffLevel) {

                        $staff_salary_payment = new StaffSalaryPayment();

                        $staff_salary_payment->school_id = $school_id;
                        $staff_salary_payment->staff_id = $each_staff->id;
                        $staff_salary_payment->staff_level_id = $each_staff->staff_level_id;
                        $staff_salary_payment->amount_paid = 0;
                        $staff_salary_payment->deduction = 0;
                        $staff_salary_payment->addition = 0;
                        $staff_salary_payment->balance = $each_staff->staffLevel->salary;
                        $staff_salary_payment->pay_month = date('F', strtotime('now')); //$pay_month;
                        $staff_salary_payment->pay_year = date('Y', strtotime('now')); //$pay_year;
                        $staff_salary_payment->recorded_by = $this->getStaff()->id;

                        $staff_salary_payment->save();

                        $each_staff->staffSalaryPayment = $staff_salary_payment;
                    } else if ($each_staff->staffLevel) {
                        //this is needed to update staff level form one to another
                        $staff_salary = $each_staff->staffLevel->salary;

                        $balance = $each_staff->staffLevel->salary - ($each_staff->staffSalaryPayment->amount_paid + $each_staff->staffSalaryPayment->deduction);

                        $each_staff->staffSalaryPayment->staff_level_id = $each_staff->staff_level_id;
                        $each_staff->staffSalaryPayment->balance = $balance;
                        if ($balance <= 0) {
                            $each_staff->staffSalaryPayment->balance = 0;
                        }

                        $each_staff->staffSalaryPayment->save();
                    }
                }
            }
        }

        $data = compact('pay_month', 'pay_year', 'staff_level_setting_error', 'level_array', 'staff', 'staff_levels');

        return $this->render('account::staff_wages.staff_payment_table', $data);
    }

    public function staffLevelSetting()
    {
        $school_id = $this->getSchool()->id;
        $staff_levels = StaffLevel::where('school_id', $school_id)->orderBy('staff_level', 'ASC')->get();

        $data = compact('staff_levels');

        return $this->render('account::staff_wages.level_settings', $data);
    }
    public function addStaffLevel(Request $request)
    {
        $school_id = $this->getSchool()->id;
        $level = $request->staff_level;
        $salary = $request->salary;
        $staff_level_id = $request->staff_level_id;

        if ($staff_level_id != "") {
            $staff_level = StaffLevel::find($staff_level_id); //for updating when edit button is clicked
        } else {
            $staff_level = StaffLevel::where(['school_id' => $school_id, 'staff_level' => $level])->first();
            if (!$staff_level) {
                $staff_level = new StaffLevel();
            }
        }


        $staff_level->school_id = $school_id;
        $staff_level->staff_level = $level;
        $staff_level->salary = $salary;
        $staff_level->save();

        $staff_levels = StaffLevel::where('school_id', $school_id)->orderBy('staff_level', 'DESC')->get();

        $data = compact('staff_levels');
        if (request()->ajax()) {

            return $this->render('account::staff_wages.level_setting_table', $data);
        }
    }



    public function staffPaymentForm($staff_id, $pay_month, $pay_year)
    {
        $each_staff = Staff::find($staff_id);

        $each_staff->staffSalaryPayment =  $each_staff->staffSalaryPayment()->where(['school_id' => $this->getschool()->id, 'pay_month' => $pay_month, 'pay_year' => $pay_year])->first();

        return $this->render('account::staff_wages.staff_payment_form', compact('each_staff', 'pay_month', 'pay_year'));
    }

    public function payStaffSalary(Request $request)
    {
        $school_id = $this->getSchool()->id;
        $main_salary = (int)($request->main_salary);
        $amount_paid = (int)($request->amount_paid);
        $deduction = (int)($request->deduction);

        $addition = (int)($request->addition);
        $recorded_by = $request->recorded_by;
        $staff_salary_id = $request->staff_salary_id;

        $staff_salary_payment = StaffSalaryPayment::find($staff_salary_id);

        $amount_after_deduction_and_addition = $amount_paid - $deduction + $addition;

        $balance = $main_salary - $amount_paid;

        $salary_balance =  $staff_salary_payment->balance;
        if ($salary_balance < $main_salary) {
            //this means some amount had been paid earlier

            $balance = $salary_balance - $amount_paid; //this is the balance
            $amount_after_deduction_and_addition = $staff_salary_payment->amount_paid + $amount_after_deduction_and_addition;
            $deduction = $staff_salary_payment->deduction + $deduction;
            $addition = $staff_salary_payment->addition + $addition;
        }

        $staff_salary_payment->balance = $balance;
        $staff_salary_payment->amount_paid = $amount_after_deduction_and_addition;
        $staff_salary_payment->deduction = $deduction;
        $staff_salary_payment->addition = $addition;

        $date = $staff_salary_payment->pay_year . '-' . date('m', strtotime($staff_salary_payment->pay_month)); //the date for the current payment
        if ($staff_salary_payment->save()) {
            //lets also update the income_and_expenses as expenses since we are giving out money

            $staff_salary_payment->school_id = $school_id;
            $staff_salary_payment->purpose = 'Staff Salary for ' . $staff_salary_payment->pay_month . ', ' . $staff_salary_payment->pay_year;
            $staff_salary_payment->date = 'now';
            $staff_salary_payment->payer_recipient_id = $staff_salary_payment->staff_id;
            $staff_salary_payment->amount_paid = $staff_salary_payment->amount_paid;
            $staff_salary_payment->payer_recipient_role = 'staff';
            $staff_salary_payment->status = 'expenses';

            $income_expenses_obj = new IncomeAndExpense();
            $income_expenses_obj->addIncomeAndExpenses($staff_salary_payment);
            //expenses table populated


            Flash::success('PAYMENT MADE SUCCESSFULLY');
            //return route('manage_staff_wages','date=2018-04');
            return redirect()->route('manage_staff_wages', 'date=' . $date);
        }
        Flash::error('PAYMENT COULD NOT BE MADE');
        //return route('manage_staff_wages','date=2018-04');
        return redirect()->route('manage_staff_wages', 'date=' . $date);
    }

    ////////////////////////////////////////////////Income n Expenses/////////////////////////////////////////////////

    public function manageIncomeExpenses(Request $request)
    {
        $school_id = $this->getSchool()->id;
        $date_to_time = strtotime('now');

        if (isset($request->date)) { //$request->date is in form of YYYY-mm i.e (2010-02)

            $date_to_time = strtotime($request->date);
        }
        $pay_year = date('Y', $date_to_time);
        $pay_month = date('F', $date_to_time);



        $income_expenses = IncomeAndExpense::where(['pay_month' => $pay_month, 'pay_year' => $pay_year])->orderBy('id', 'DESC')->get();
        //$incomes = Income::where(['pay_month'=>$pay_month, 'pay_year'=>$pay_year])->get();

        if (isset($request->date) && $request->date == 'all') {
            $income_expenses = IncomeAndExpense::orderBy('id', 'DESC')->get();
            //$incomes = Income::all();
        }
        $total_income = 0;
        $total_expenses = 0;

        foreach ($income_expenses as $income_expense) {


            if ($income_expense->payer_recipient_role == 'student') {
                $income_expense->user = User::find($income_expense->student->user_id);
            } elseif ($income_expense->payer_recipient_role == 'staff') {
                $income_expense->user = User::find($income_expense->staff->user_id);
            }
            $income_expense->role = $income_expense->payer_recipient_role;


            if ($income_expense->status == 'expenses') {

                $total_expenses += $income_expense->amount;
            } elseif ($income_expense->status == 'income') {

                $total_income += $income_expense->amount;
            }
        }


        $profit = $total_income - $total_expenses;
        //asort($users);
        $recipient_role = ['' => 'Select', 'staff' => 'Staff', 'student' => 'Student']; //this is needed for new expenses entry

        $data = compact('income_expenses', 'pay_year', 'pay_month', 'total_income', 'total_expenses', 'profit', 'recipient_role');

        if (request()->ajax()) {

            //if we load this page via ajax request when we are selecting date
            return $this->render('account::expenses.monthly_income_expenses', $data);
        }

        return $this->render('account::expenses.income_expenses', $data);
        //return $users;
    }

    public function addExpenses(Request $request)
    {
        $school_id = $this->getSchool()->id;


        $request->school_id = $school_id;
        $income_expenses_obj = new IncomeAndExpense();
        //$request->status = "expenses";
        $income_expenses_obj->addIncomeAndExpenses($request, true);


        Flash::success('EXPENSES MADE SUCCESSFULLY');
        //return route('manage_staff_wages','date=2018-04');
        return redirect()->route('income_n_expenses');
    }

    public function getExpensesRecipient(Request $request)
    {
        if ($request->role == 'student') {
            $recipients = Student::where('studentship_status', 'active')->get();
        } else {
            $recipients = Staff::where('is_active', '1')->get();
        }

        $selected_recipients = [];
        foreach ($recipients as $recipient) :
            $selected_recipients[] = ['id' => $recipient->id, 'name' => $recipient->user->last_name . ' ' . $recipient->user->first_name];
        endforeach;



        $fetched_data = $selected_recipients;

        return json_encode($fetched_data);
    }

    public function deleteExpenses($id)
    {
        $expenses = IncomeAndExpense::find($id);
        if ($expenses->deletable == '1') {
            $expenses->delete();
            Flash::warning('DATA DELETED SUCCESSFULLY');
        } else {
            Flash::error('DATA CANNOT BE DELETED');
        }




        //return route('manage_staff_wages','date=2018-04');
        return redirect()->route('income_n_expenses');
    }

    public function getStudentOwingExtraFee(Request $request)
    {
        $school_id = $this->getSchool()->id;
        $other_fee_id = $request->other_fee_id;
        $sess_id = $request->sess_id;
        $term_id = $request->term_id;
        $other_fee = OtherFee::find($other_fee_id);

        $level_id = $other_fee->level_id;
        $students = Student::where('current_level', $level_id)->get();
        $owing_student_array = [];
        foreach ($students as $student) {
            //check for the recurrence of the fee and whether or not the student has paid
            if ($other_fee->recurrence == "Termly") {
                $other_fee_payment = OtherFeePayment::where(['school_id' => $school_id, 'term_id' => $term_id, 'sess_id' => $sess_id, 'student_id' => $student->id])->first();
            } else {
                $other_fee_payment = OtherFeePayment::where(['school_id' => $school_id, 'sess_id' => $sess_id, 'student_id' => $student->id])->first();
            }
            if (!$other_fee_payment) {
                //student has not paid...so we fetch him/her out
                $owing_student_array[] = ['id' => $student->id, 'details' => $student->registration_no . ' | ' . $student->user->first_name . '  ' . $student->user->last_name];
            }
        }

        $fetched_data = $owing_student_array;

        return json_encode($fetched_data);
    }

    public function payStudentOtherFee(Request $request)
    {
        $school_id = $this->getSchool()->id;
        $other_fee_id = $request->other_fee_id;
        $sess_id = $request->sess_id;
        $term_id = $request->term_id;
        $student_ids = $request->student_ids;
        $other_fee = OtherFee::find($other_fee_id);
        $amount = $other_fee->amount;



        foreach ($student_ids as $student_id) {
            $other_fee_payment = OtherFeePayment::where(['term_id' => $term_id, 'sess_id' => $sess_id, 'student_id' => $student_id])->first();
            if (!$other_fee_payment) {
                $other_fee_payment = new OtherFeePayment();
            }


            $other_fee_payment->school_id = $school_id;
            $other_fee_payment->student_id = $student_id;
            $other_fee_payment->other_fee_id = $other_fee_id;
            $other_fee_payment->amount_paid = $amount;
            $other_fee_payment->sess_id = $sess_id;
            $other_fee_payment->term_id = $term_id;
            $other_fee_payment->save();
        }
        //fetch other fee payments


        return response()->json([], 200);
    }

    // public function deleteOtherFeePayment(Request $request)
    // {
    //     $school_id = $this->getSchool()->id;
    //     $other_fee_payment = OtherFeePayment::find($request->id);
    //     $other_fee_payment->delete();

    //     $other_fee_payments = OtherFeePayment::where('school_id', $school_id)->orderBy('id', 'DESC')->get();
    //     if (request()->ajax()) {
    //         if ($other_fee_payments != '[]') {
    //             foreach ($other_fee_payments as $other_fee_payment) {
    //                 $other_fee_payment->user = User::find($other_fee_payment->student->user_id);
    //                 $other_fee_payment->level = Level::find($other_fee_payment->otherFee->level_id);
    //             }
    //         }
    //         $data = compact('other_fee_payments');
    //         //if we load this page via ajax request when we are selecting term and session
    //         return $this->render('account::schoolfees.other_fee_collection_table', $data);
    //     }
    // }
}
