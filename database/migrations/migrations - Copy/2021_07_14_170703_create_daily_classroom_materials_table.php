<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDailyClassroomMaterialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('daily_classroom_materials', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('daily_classroom_id');
            $table->string('file_link');
            $table->string('file_name', 50);
            $table->text('viewers')->nullable();
            $table->string('mime')->nullable();
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
        Schema::dropIfExists('daily_classroom_materials');
    }
}
