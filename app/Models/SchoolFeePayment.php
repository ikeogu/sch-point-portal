<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolFeePayment extends Model
{
    //
    public function staff() {
        return $this->belongsTo(Staff::class, 'logged_by', 'id');
    }

    public function paymentMonitor() {
        return $this->belongsTo(FeePaymentMonitor::class);
    }
}
