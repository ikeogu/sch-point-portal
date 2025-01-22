<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LibraryBook extends Model
{
    use HasFactory, SoftDeletes;

    public function borrowedBooks()
    {
        return $this->hasMany(LibraryBorrowedBook::class, 'library_book_id', 'id');
    }
    public function levelGroup()
    {
        return $this->belongsTo(CurriculumLevelGroup::class, 'curriculum_level_group_id', 'id');
    }
    public function category()
    {
        return $this->belongsTo(LibraryBookCategory::class, 'library_book_category_id', 'id');
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}
