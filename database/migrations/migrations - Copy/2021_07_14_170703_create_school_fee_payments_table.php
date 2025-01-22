<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSchoolFeePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('school_fee_payments', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('fee_payment_monitor_id');
            $table->integer('amount');
            $table->integer('student_id');
            $table->string('receipt_no', 50)->nullable()->unique('receipt_no');
            $table->date('pay_date')->nullable();
            $table->integer('logged_by')->nullable();
            $table->enum('remitted', ['0', '1']);
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
        Schema::dropIfExists('school_fee_payments');
    }
}
