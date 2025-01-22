<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransportationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transportations', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('school_id');
            $table->string('route');
            $table->string('vehicle_no', 100);
            $table->string('driver_name', 100);
            $table->string('license_no', 100)->nullable();
            $table->integer('no_of_seats');
            $table->string('driver_phone', 25);
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
        Schema::dropIfExists('transportations');
    }
}
