<?php

namespace App\Http\Controllers\Account;

use App\Models\IncomeAndExpense;
use App\Models\Staff;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Carbon\Carbon as Carbon;

class AccountController extends Controller
{
    public function incomeAndExpenses(Request $request)
    {
        $school_id = $this->getSchool()->id;
        $date_to_time = strtotime('now');

        if (isset($request->date)) { //$request->date is in form of YYYY-mm i.e (2010-02)

            $date_to_time = strtotime($request->date);
        }
        $pay_year = date('Y', $date_to_time);
        $pay_month = date('F', $date_to_time);
        $extra_condition = [];
        if (isset($request->query_type) && $request->query_type === 'approved') {
            $extra_condition = ['deletable' => '0'];
        }


        $income_expenses = IncomeAndExpense::with('user')->where(['pay_month' => $pay_month, 'pay_year' => $pay_year])->where($extra_condition)->orderBy('id', 'DESC')->get();
        $data = compact('income_expenses');


        return $this->render($data);
        //return $users;
    }

    public function addIncomeExpenses(Request $request)
    {
        $request->school_id = $this->getSchool()->id;
        $request->term_id = $this->getTerm()->id;
        $request->sess_id = $this->getSession()->id;
        $income_expenses_obj = new IncomeAndExpense();
        //$request->status = "expenses";
        $income_expenses_obj->addIncomeAndExpenses($request, true);

        return response()->json([], 200);
        // Flash::success('EXPENSES MADE SUCCESSFULLY');
        // //return route('manage_staff_wages','date=2018-04');
        // return redirect()->route('income_n_expenses');
    }

    public function getExpensesRecipient(Request $request)
    {
        $school_id = $this->getSchool()->id;

        $students = Student::with('user')->where('school_id', $school_id)->where('studentship_status', 'active')->get();

        $staff = Staff::with('user')->where('school_id', $school_id)->where('is_active', '1')->get();

        return $this->render(compact('students', 'staff'));
    }
    public function approveIncomeExpense(IncomeAndExpense $income_expense)
    {
        $income_expense->deletable = '0';
        $income_expense->save();
        $income_expense = $income_expense->with('user')->find($income_expense->id);
        return response()->json(compact('income_expense'), 200);
    }
    public function deleteExpenses(IncomeAndExpense $income_expense)
    {
        if ($income_expense->deletable == '1') {
            $income_expense->delete();
        }
        return response()->json([], 204);
    }

    public function statementOfAccount(Request $request)
    {
        $school_id = $this->getSchool()->id;
        $date_from = Carbon::now()->startOfMonth();
        $date_to = Carbon::now()->endOfMonth();
        $panel = 'month';
        if (isset($request->from, $request->to)) {
            $date_from = date('Y-m-d', strtotime($request->from)) . ' 00:00:00';
            $date_to = date('Y-m-d', strtotime($request->to)) . ' 23:59:59';
            $panel = $request->panel;
        }
        $warehouse_id = $request->warehouse_id;
        $item_id = $request->item_id;
        list($past_income, $past_expenses, $current_incomes_expenses) = $this->getTransactions($school_id, $date_from, $date_to);

        $statements = [];
        $past_income = ($past_income) ? $past_income->total_income : 0;
        $past_expenses = ($past_expenses) ? $past_expenses->total_expenses : 0;

        $brought_forward = (int)$past_income - (int) $past_expenses;
        if ($current_incomes_expenses->isNotEmpty()) {
            foreach ($current_incomes_expenses as $income_expense) {
                //$running_balance += $inbound->quantity;
                $type = $income_expense->status;
                $statements[]  = [
                    'type' => $type,
                    'date' => $income_expense->date,
                    'log' => $income_expense->created_at,
                    'ref' => $income_expense->user->first_name . ' ' . $income_expense->user->last_name . ' ' . $income_expense->user->username,
                    'amount' => $income_expense->amount,
                    'credit' => ($type === 'income') ? $income_expense->amount : '',
                    'debit' => ($type === 'expenses') ? $income_expense->amount : '',
                    'purpose' => $income_expense->purpose,
                    'balance' => 0, // initially set to zero
                ];
            }
        }

        // usort($statements, function ($a, $b) {
        //     return strtotime($a['date']) - strtotime($b['date']);
        // });
        $date_from_formatted = date('Y-m-d', strtotime($date_from));
        $date_to_formatted = date('Y-m-d', strtotime($date_to));
        return  $this->render(compact('statements', 'brought_forward', 'date_from_formatted', 'date_to_formatted'));
    }

    private function getTransactions($school_id, $date_from, $date_to)
    {

        $past_income = IncomeAndExpense::groupBy('status')
            ->where('school_id', $school_id)
            ->where('status', 'income')
            ->where('created_at', '<', $date_from)
            ->where('deletable', '0')
            ->select('*', \DB::raw('SUM(amount) as total_income'))
            ->first();
        $past_expenses = IncomeAndExpense::groupBy('status')
            ->where('school_id', $school_id)
            ->where('status', 'expenses')
            ->where('created_at', '<', $date_from)
            ->where('deletable', '0')
            ->select('*', \DB::raw('SUM(amount) as total_expenses'))
            ->first();

        $current_incomes_expenses = IncomeAndExpense::with('user')
            ->where(['school_id' => $school_id])
            ->where('created_at', '>=', $date_from)
            ->where('created_at', '<=', $date_to)
            ->where('deletable', '0')
            ->orderby('created_at')
            ->get();

        return array($past_income, $past_expenses, $current_incomes_expenses);
    }
}
