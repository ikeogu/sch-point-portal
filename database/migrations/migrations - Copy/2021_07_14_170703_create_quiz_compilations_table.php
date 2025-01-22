<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuizCompilationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quiz_compilations', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('school_id');
            $table->integer('subject_teacher_id');
            $table->integer('teacher_id');
            $table->integer('sess_id');
            $table->integer('term_id');
            $table->enum('question_type', ['objective', 'theory'])->default('objective');
            $table->string('instructions')->nullable();
            $table->integer('duration')->nullable();
            $table->integer('point');
            $table->enum('status', ['inactive', 'active'])->default('Inactive');
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
        Schema::dropIfExists('quiz_compilations');
    }
}
