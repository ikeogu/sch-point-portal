<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOtherFeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('other_fees', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('school_id')->nullable();
            $table->integer('amount')->nullable();
            $table->string('purpose', 100);
            $table->integer('level_id');
            $table->enum('is_active', ['0', '1']);
            $table->enum('recurrence', ['termly', 'once per session']);
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
        Schema::dropIfExists('other_fees');
    }
}
