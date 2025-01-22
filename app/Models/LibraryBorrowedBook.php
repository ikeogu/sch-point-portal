<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LibraryBorrowedBook extends Model
{
    use HasFactory;
    public function libraryBook()
    {
        return $this->belongsTo(LibraryBook::class);
    }
    public function borrower()
    {
        return $this->belongsTo(User::class, 'borrower_id', 'id');
    }
    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by', 'id');
    }
    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by', 'id');
    }
}
