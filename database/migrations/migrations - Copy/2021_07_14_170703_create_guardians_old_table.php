<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGuardiansOldTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('guardians_old', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('school_id');
            $table->unsignedInteger('user_id')->unique('user_id');
            $table->string('occupation', 30)->nullable();
            $table->text('ward_ids');
            $table->text('ward_details');
            $table->enum('is_active', ['0', '1'])->default('1');
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
        Schema::dropIfExists('guardians_old');
    }
}
