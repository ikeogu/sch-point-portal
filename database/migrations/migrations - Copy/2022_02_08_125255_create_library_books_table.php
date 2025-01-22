<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLibraryBooksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('library_books', function (Blueprint $table) {
            $table->id();
            $table->integer('school_id');
            $table->integer('library_book_category_id');
            $table->string('ISBN')->nullable();
            $table->string('title');
            $table->string('authors')->nullable();
            $table->string('serial_no')->nullable();
            $table->string('publisher')->nullable();
            $table->year('copyright_year')->nullable();
            $table->integer('no_of_pages')->nullable();
            $table->integer('quantity');
            $table->text('description');
            $table->integer('created_by');
            $table->timestamps();
        });

        Schema::create('library_book_categories', function (Blueprint $table) {
            $table->id();
            $table->integer('school_id');
            $table->string('name');
            $table->text('description');
            $table->timestamps();
        });

        Schema::create('library_borrowed_books', function (Blueprint $table) {
            $table->id();
            $table->integer('school_id');
            $table->integer('library_book_id');
            $table->integer('borrower_id');
            $table->date('date_borrowed');
            $table->date('due_date');
            $table->integer('quantity')->default(1);
            $table->integer('processed_by');
            $table->enum('is_returned', ['yes', 'no'])->default('no');
            $table->integer('quantity_returned')->default(1);
            $table->date('date_returned')->nullable();
            $table->integer('received_by')->nullable();
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
        Schema::dropIfExists('library_books');
        Schema::dropIfExists('library_book_categories');
        Schema::dropIfExists('library_borrowed_books');
    }
}
