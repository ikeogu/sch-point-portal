<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePotentialSchoolsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('potential_schools', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 225)->unique('schools_name_unique');
            $table->string('estimated_no_of_students', 20)->nullable();
            $table->string('website', 50)->nullable();
            $table->string('slug', 20)->nullable();
            $table->string('sub_domain', 20)->nullable();
            $table->string('lga', 30)->nullable();
            $table->string('email', 191)->nullable()->unique('schools_email_unique');
            $table->string('phone', 191)->nullable()->unique('phone');
            $table->string('address', 191)->nullable();
            $table->string('curriculum', 191)->nullable();
            $table->string('logo', 191)->nullable();
            $table->string('admin_first_name', 18)->nullable();
            $table->string('admin_last_name', 18)->nullable();
            $table->string('admin_email', 50)->nullable();
            $table->string('admin_phone2', 15)->nullable();
            $table->string('admin_phone1', 18)->nullable();
            $table->string('admin_gender', 6)->nullable();
            $table->string('folder_key', 50)->nullable();
            $table->enum('is_active', ['0', '1'])->default('0');
            $table->timestamp('date')->nullable();
            $table->dateTime('deleted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('potential_schools');
    }
}
