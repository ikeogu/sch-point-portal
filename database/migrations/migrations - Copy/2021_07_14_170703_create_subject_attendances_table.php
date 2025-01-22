<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSubjectAttendancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subject_attendances', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('school_id');
            $table->integer('subject_teacher_id');
            $table->text('student_ids')->nullable();
            $table->text('absent_students')->nullable();
            $table->integer('sess_id');
            $table->integer('term_id');
            $table->date('date')->nullable();
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
        Schema::dropIfExists('subject_attendances');
    }
}
