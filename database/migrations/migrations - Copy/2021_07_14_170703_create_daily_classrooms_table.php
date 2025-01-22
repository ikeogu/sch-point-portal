<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDailyClassroomsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('daily_classrooms', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('school_id');
            $table->integer('class_teacher_id');
            $table->integer('subject_teacher_id');
            $table->string('topic');
            $table->text('description');
            $table->longText('class_note')->nullable();
            $table->integer('duration');
            $table->string('date', 50)->nullable();
            $table->string('start', 20);
            $table->string('end', 20);
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
        Schema::dropIfExists('daily_classrooms');
    }
}
