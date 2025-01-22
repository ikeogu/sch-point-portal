<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStudentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('students', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unique('user_id');
            $table->unsignedInteger('school_id');
            $table->unsignedInteger('class_id')->nullable();
            $table->string('registration_no', 191)->unique('registration_no');
            $table->string('admission_year', 15)->nullable();
            $table->integer('level_admitted')->nullable();
            $table->integer('current_level')->nullable();
            $table->integer('admission_sess_id');
            $table->boolean('is_prev_cert_submitted')->nullable();
            $table->boolean('is_transfer_cert_submitted')->nullable();
            $table->boolean('is_academic_transcript_submitted')->nullable();
            $table->boolean('is_national_birth_cert_submitted')->nullable();
            $table->boolean('is_testimonial_submitted')->nullable();
            $table->enum('studentship_status', ['active', 'left', 'graduated', 'suspended']);
            $table->enum('is_active', ['0', '1'])->default('1');
            $table->boolean('suspended_for_nonpayment')->default(0);
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
        Schema::dropIfExists('students');
    }
}
