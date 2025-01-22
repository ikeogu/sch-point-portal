<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDailyClassroomVideosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('daily_classroom_videos', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('daily_classroom_id');
            $table->integer('youtube_video_id')->nullable();
            $table->string('link')->nullable();
            $table->string('param')->nullable();
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
        Schema::dropIfExists('daily_classroom_videos');
    }
}
