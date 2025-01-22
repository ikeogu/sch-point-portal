<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePartnerAccountDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('partner_account_details', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('partner_id')->unique('partner_id');
            $table->string('bank', 30);
            $table->string('account_name', 30);
            $table->string('account_no', 30);
            $table->string('account_type', 15);
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
        Schema::dropIfExists('partner_account_details');
    }
}
