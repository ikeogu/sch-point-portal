<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('school_id');
            $table->integer('subject_teacher_id');
            $table->integer('teacher_id');
            $table->longText('question');
            $table->string('optA')->nullable();
            $table->string('optB')->nullable();
            $table->string('optC')->nullable();
            $table->string('optD')->nullable();
            $table->string('optE')->nullable();
            $table->string('answer', 30);
            $table->enum('question_type', ['multi_choice', 'true_false'])->nullable()->default('multi_choice');
            $table->integer('point')->nullable();
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
        Schema::dropIfExists('questions');
    }
}
