<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAssignmentStudentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('assignment_students', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('school_id');
            $table->unsignedInteger('student_id');
            $table->unsignedInteger('assignment_id');
            $table->timestamp('date')->nullable();
            $table->string('answer_link', 191)->nullable();
            $table->longText('student_answer')->nullable();
            $table->tinyInteger('score')->nullable();
            $table->text('remark')->nullable();
            $table->integer('sess_id');
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
        Schema::dropIfExists('assignment_students');
    }
}
