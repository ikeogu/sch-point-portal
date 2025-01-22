<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSchoolsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('schools', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('group_of_school_id')->nullable();
            $table->string('name', 225)->unique('schools_name_unique');
            $table->string('slug', 20);
            $table->string('sub_domain', 20)->nullable();
            $table->integer('lga_id')->nullable();
            $table->string('email', 191)->nullable()->unique('schools_email_unique');
            $table->string('phone', 191)->nullable();
            $table->string('address', 191);
            $table->string('curriculum', 191)->nullable();
            $table->enum('nursery', ['0', '1'])->default('0');
            $table->enum('pry', ['0', '1'])->default('0');
            $table->enum('secondary', ['0', '1'])->default('0');
            $table->string('logo', 191)->nullable();
            $table->string('logo_junior')->nullable();
            $table->string('logo_senior')->nullable();
            $table->string('main_bg')->nullable();
            $table->string('result_bg')->nullable();
            $table->string('navbar_bg', 25)->nullable()->default('#3c8dbc');
            $table->string('sidebar_bg', 25)->nullable()->default('#222d32');
            $table->string('logo_bg', 25)->default('#d95043');
            $table->string('mime', 191)->nullable();
            $table->tinyInteger('current_term')->nullable();
            $table->integer('current_session')->nullable();
            $table->string('active_assessment', 100)->nullable();
            $table->string('preferred_template', 50)->nullable();
            $table->enum('display_student_position', ['1', '0'])->default('1');
            $table->enum('is_active', ['0', '1'])->default('1');
            $table->string('folder_key', 50)->nullable();
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
        Schema::dropIfExists('schools');
    }
}
