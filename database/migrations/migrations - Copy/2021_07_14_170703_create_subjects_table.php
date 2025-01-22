<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSubjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subjects', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('school_id');
            $table->string('name', 191);
            $table->string('code', 191)->nullable();
            $table->integer('curriculum_level_group_id');
            $table->string('color_code', 11)->nullable();
            $table->string('subject_group', 191)->nullable();
            $table->enum('is_mock', ['0', '1'])->nullable();
            $table->string('img', 100)->nullable();
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
        Schema::dropIfExists('subjects');
    }
}
