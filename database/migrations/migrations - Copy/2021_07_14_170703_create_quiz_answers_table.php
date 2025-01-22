<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuizAnswersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quiz_answers', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer('quiz_attempt_id')->nullable();
            $table->integer('student_id')->nullable();
            $table->string('registration_no', 50)->nullable();
            $table->longText('student_answer')->nullable();
            $table->integer('question_id')->nullable();
            $table->integer('theory_question_id')->nullable();
            $table->integer('point_earned')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('quiz_answers');
    }
}
