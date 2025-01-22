<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePartnersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('partners', function (Blueprint $table) {
            $table->increments('id');
            $table->string('first_name', 191);
            $table->string('last_name', 191);
            $table->string('email', 100)->unique('users_email_unique');
            $table->string('partner_username', 25)->unique('username');
            $table->string('password');
            $table->enum('password_status', ['default', 'custom']);
            $table->string('my_ref', 20)->nullable();
            $table->integer('referrer_id')->nullable();
            $table->string('phone1', 50)->nullable();
            $table->string('phone2', 191)->nullable();
            $table->enum('gender', ['female', 'male']);
            $table->text('address')->nullable();
            $table->string('photo', 191);
            $table->string('mime', 100)->nullable();
            $table->rememberToken();
            $table->string('confirm_hash')->nullable();
            $table->enum('is_confirmed', ['0', '1'])->default('0');
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
        Schema::dropIfExists('partners');
    }
}
