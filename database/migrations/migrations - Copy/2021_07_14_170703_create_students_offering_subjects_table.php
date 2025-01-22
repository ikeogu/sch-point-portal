<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStudentsOfferingSubjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('students_offering_subjects', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('school_id');
            $table->text('student_ids')->nullable();
            $table->integer('subject_teacher_id');
            $table->integer('sess_id')->nullable();
            $table->integer('term_id');
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
        Schema::dropIfExists('students_offering_subjects');
    }
}
