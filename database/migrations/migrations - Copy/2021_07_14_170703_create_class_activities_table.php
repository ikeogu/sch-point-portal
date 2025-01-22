<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClassActivitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('class_activities', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('school_id');
            $table->integer('class_teacher_id');
            $table->integer('actor_id')->nullable();
            $table->string('name', 35)->nullable();
            $table->string('role', 10)->nullable();
            $table->longText('action_details')->nullable();
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
        Schema::dropIfExists('class_activities');
    }
}
