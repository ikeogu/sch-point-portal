<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('package_modules', function (Blueprint $table) {
            $table->id();
            $table->integer('package_id');
            $table->integer('module_id');
            $table->timestamps();
        });
        Schema::create('package_schools', function (Blueprint $table) {
            $table->id();
            $table->integer('package_id');
            $table->integer('school_id');
            $table->integer('added_by');
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
        Schema::dropIfExists('packages');
        Schema::dropIfExists('package_modules');
        Schema::dropIfExists('package_schools');
    }
}
