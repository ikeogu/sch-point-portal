<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class IncomeAndExpense extends Model
{
    use SoftDeletes;
    //
    public function user()
    {
        return $this->belongsTo(User::class, 'payer_recipient_id', 'id');
    }

    public function addIncomeAndExpenses($data, $deletable = false)
    {
        $expenses = new IncomeAndExpense();
        $expenses->school_id = $data->school_id;
        $expenses->term_id = $data->term_id;
        $expenses->sess_id = $data->sess_id;
        $expenses->purpose = $data->purpose; //'Staff Salary for '.$staff_salary_payment->pay_month.', '.$staff_salary_payment->pay_year;
        $expenses->amount = $data->amount_paid;
        $expenses->payer_recipient_id = $data->payer_recipient_id;
        $expenses->payer_recipient_role = $data->payer_recipient_role;
        $expenses->status = $data->status;
        if ($deletable) {
            $expenses->deletable = '1'; //It is '0' by default in the db
        }
        $expenses->pay_month = date('F', strtotime($data->date));
        $expenses->pay_year = date('Y', strtotime($data->date));
        $expenses->date = date('Y-m-d', strtotime($data->date));

        // if ($expenses->status == 'income') {
        // $expenses->created_at = date('Y-m-d H:i:s', strtotime($data->date));
        // $expenses->updated_at = date('Y-m-d H:i:s', strtotime($data->date));
        // }

        $expenses->save();
    }
}
