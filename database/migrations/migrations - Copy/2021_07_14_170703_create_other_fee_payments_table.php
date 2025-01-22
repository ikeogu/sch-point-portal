<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOtherFeePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('other_fee_payments', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('school_id');
            $table->integer('student_id');
            $table->integer('other_fee_id')->nullable();
            $table->integer('amount_paid');
            $table->integer('sess_id');
            $table->integer('term_id');
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
        Schema::dropIfExists('other_fee_payments');
    }
}
