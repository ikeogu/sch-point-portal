<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSubjectActivitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subject_activities', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('subject_teacher_id');
            $table->integer('teacher_id')->nullable();
            $table->integer('school_id');
            $table->longText('action_details')->nullable();
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
        Schema::dropIfExists('subject_activities');
    }
}
