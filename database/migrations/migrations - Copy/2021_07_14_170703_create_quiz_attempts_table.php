<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuizAttemptsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('student_id');
            $table->integer('quiz_compilation_id');
            $table->enum('has_submitted', ['no', 'yes'])->default('no');
            $table->integer('remaining_time');
            $table->integer('score_limit')->nullable();
            $table->float('student_point')->nullable();
            $table->float('percent_score')->nullable();
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
        Schema::dropIfExists('quiz_attempts');
    }
}
