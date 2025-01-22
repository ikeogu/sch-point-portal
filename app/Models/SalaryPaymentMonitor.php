<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryPaymentMonitor extends Model
{
    use HasFactory;
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
    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function salaryScale()
    {
        return $this->belongsTo(SalaryScale::class);
    }

    public function payments()
    {
        return $this->hasMany(StaffSalaryPayment::class, 'salary_payment_monitor_id', 'id');
    }
}
