<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIncomeAndExpensesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('income_and_expenses', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('school_id');
            $table->string('purpose');
            $table->integer('amount');
            $table->integer('payer_recipient_id');
            $table->string('status', 10)->nullable();
            $table->string('payer_recipient_role', 10);
            $table->string('pay_month', 15)->nullable();
            $table->year('pay_year', 4)->nullable();
            $table->enum('deletable', ['0', '1']);
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
        Schema::dropIfExists('income_and_expenses');
    }
}
