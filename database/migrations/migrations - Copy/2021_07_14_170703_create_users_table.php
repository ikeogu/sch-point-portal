<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('last_name', 191);
            $table->string('first_name', 191);
            $table->string('email', 100)->nullable()->unique('users_email_unique');
            $table->string('username', 100)->nullable()->unique('username');
            $table->string('password');
            $table->enum('password_status', ['default', 'custom'])->default('default');
            $table->string('phone1', 50)->nullable();
            $table->string('phone2', 191)->nullable();
            $table->string('gender', 10);
            $table->string('role', 15)->nullable();
            $table->text('address')->nullable();
            $table->date('dob')->nullable();
            $table->string('disablility', 15)->nullable();
            $table->integer('country_id')->nullable();
            $table->integer('state_id')->nullable();
            $table->integer('lga_id')->nullable();
            $table->string('religion', 30)->nullable();
            $table->string('photo', 191)->nullable();
            $table->string('mime', 100)->nullable();
            $table->rememberToken();
            $table->string('confirm_hash')->nullable();
            $table->enum('is_confirmed', ['1', '0'])->default('1');
            $table->timestamps();
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
        Schema::dropIfExists('users');
    }
}
