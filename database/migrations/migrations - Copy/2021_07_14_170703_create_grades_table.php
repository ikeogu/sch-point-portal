<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGradesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('grades', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('school_id');
            $table->integer('curriculum_level_group_id');
            $table->string('grade', 3);
            $table->string('interpretation', 50)->nullable();
            $table->string('grade_range')->nullable();
            $table->float('upper_limit')->nullable();
            $table->float('lower_limit')->nullable();
            $table->float('grade_point');
            $table->string('color_code');
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
        Schema::dropIfExists('grades');
    }
}
