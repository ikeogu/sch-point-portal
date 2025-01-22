<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateResults2Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('results2', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('school_id');
            $table->integer('class_teacher_id');
            $table->integer('sess_id');
            $table->integer('student_id');
            $table->mediumText('grade_term_1')->nullable();
            $table->mediumText('subject_term_1')->nullable();
            $table->mediumText('grade_term_2')->nullable();
            $table->mediumText('subject_term_2')->nullable();
            $table->mediumText('grade_term_3')->nullable();
            $table->mediumText('subject_term_3')->nullable();
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
        Schema::dropIfExists('results2');
    }
}
