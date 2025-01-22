<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('original_message_id')->nullable();
            $table->unsignedInteger('sender');
            $table->unsignedInteger('recipient');
            $table->mediumText('copied_to')->nullable();
            $table->string('subject', 191);
            $table->mediumText('message');
            $table->longText('replies')->nullable();
            $table->text('read_by')->nullable();
            $table->integer('sender_delete')->nullable();
            $table->text('recipient_delete')->nullable();
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
        Schema::dropIfExists('messages');
    }
}
