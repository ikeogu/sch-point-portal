<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTheoryQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('theory_questions', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('school_id');
            $table->integer('subject_teacher_id');
            $table->integer('teacher_id');
            $table->longText('question');
            $table->integer('point');
            $table->string('question_type', 10)->nullable()->default('theory');
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
        Schema::dropIfExists('theory_questions');
    }
}
