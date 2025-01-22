<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStaffTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('staff', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('school_id');
            $table->integer('staff_level_id')->nullable()->default(1);
            $table->string('job_type', 50)->nullable();
            $table->boolean('is_cv_submitted')->nullable();
            $table->boolean('is_edu_cert_submitted')->nullable();
            $table->boolean('is_exp_cert_submitted')->nullable();
            $table->enum('is_active', ['0', '1'])->default('1');
            $table->date('appointment_date')->nullable();
            $table->timestamps();
            $table->date('deleted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('staff');
    }
}
