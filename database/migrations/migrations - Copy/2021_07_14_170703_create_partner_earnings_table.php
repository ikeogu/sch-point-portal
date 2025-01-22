<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePartnerEarningsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('partner_earnings', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('partner_id');
            $table->integer('school_id');
            $table->integer('sess_id')->nullable();
            $table->integer('term_id')->nullable();
            $table->integer('expected_amount')->nullable();
            $table->integer('amount_paid')->nullable();
            $table->enum('acknowledged', ['0', '1'])->default('0');
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
        Schema::dropIfExists('partner_earnings');
    }
}
