<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBehaviorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('behaviors', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('school_id');
            $table->integer('student_id');
            $table->string('reg_no', 25);
            $table->integer('sess_id');
            $table->integer('term_id');
            $table->integer('attentiveness')->nullable();
            $table->integer('calmness')->nullable();
            $table->integer('honesty')->nullable();
            $table->integer('neatness')->nullable();
            $table->integer('punctuality')->nullable();
            $table->integer('perseverance')->nullable();
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
        Schema::dropIfExists('behaviors');
    }
}
