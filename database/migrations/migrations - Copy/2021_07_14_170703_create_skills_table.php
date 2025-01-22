<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSkillsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('skills', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('school_id');
            $table->integer('student_id');
            $table->string('reg_no', 25);
            $table->integer('sess_id');
            $table->integer('term_id');
            $table->integer('leadership')->nullable();
            $table->integer('initiative')->nullable();
            $table->integer('art_works')->nullable();
            $table->integer('spoken_english')->nullable();
            $table->integer('sports')->nullable();
            $table->integer('tools_handling')->nullable();
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
        Schema::dropIfExists('skills');
    }
}
