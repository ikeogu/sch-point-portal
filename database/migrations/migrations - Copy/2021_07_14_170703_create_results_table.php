<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('results', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('school_id');
            $table->integer('class_teacher_id');
            $table->integer('recorded_by');
            $table->integer('sess_id');
            $table->integer('student_id');
            $table->string('reg_no', 25)->nullable();
            $table->integer('term_id')->nullable();
            $table->integer('subject_teacher_id')->nullable();
            $table->integer('mid_term')->nullable();
            $table->integer('ca1')->nullable();
            $table->integer('ca2')->nullable();
            $table->integer('ca3')->nullable();
            $table->integer('exam')->nullable();
            $table->float('total')->nullable();
            $table->integer('effort')->nullable();
            $table->integer('behavior')->nullable();
            $table->text('comments')->nullable();
            $table->enum('result_status', ['applicable', 'not applicable'])->default('Applicable');
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
        Schema::dropIfExists('results');
    }
}
