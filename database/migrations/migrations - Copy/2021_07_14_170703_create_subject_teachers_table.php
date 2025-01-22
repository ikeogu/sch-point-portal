<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSubjectTeachersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subject_teachers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('school_id');
            $table->integer('subject_id')->index('subject_teacher_subject_id_index');
            $table->integer('teacher_id')->nullable()->index('subject_teacher_teacher_id_index');
            $table->integer('class_teacher_id')->nullable();
            $table->mediumText('student_ids')->nullable();
            $table->text('result_action')->nullable();
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
        Schema::dropIfExists('subject_teachers');
    }
}
