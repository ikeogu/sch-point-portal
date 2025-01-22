<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateResultDisplaySettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('result_display_settings', function (Blueprint $table) {
            $table->id();
            $table->integer('school_id');
            $table->integer('curriculum_level_group_id');
            $table->integer('no_of_ca')->default(4);
            $table->integer('ca1')->default(10);
            $table->integer('ca2')->default(10);
            $table->integer('ca3')->default(10);
            $table->integer('ca4')->default(10);
            $table->integer('ca5')->nullable();
            $table->integer('exam')->default(60);
            $table->integer('no_of_ca_for_midterm')->default(2);
            $table->enum('display_student_position', ['yes', 'no'])->default('no');
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
        Schema::dropIfExists('result_display_settings');
    }
}
