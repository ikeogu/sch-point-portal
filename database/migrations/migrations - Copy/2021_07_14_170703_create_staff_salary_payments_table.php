<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStaffSalaryPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('staff_salary_payments', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('school_id');
            $table->integer('staff_id');
            $table->integer('staff_level_id');
            $table->integer('amount_paid');
            $table->integer('deduction')->nullable();
            $table->integer('addition')->nullable();
            $table->integer('balance')->nullable();
            $table->string('pay_month', 10);
            $table->year('pay_year', 4);
            $table->integer('recorded_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('staff_salary_payments');
    }
}
