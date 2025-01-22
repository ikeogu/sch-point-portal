<?php

namespace App\Http\Controllers\Library;

use App\Http\Controllers\Controller;
use App\Models\CurriculumLevelGroup;
use App\Models\Level;
use App\Models\LibraryBook;
use App\Models\LibraryBookCategory;
use App\Models\LibraryBorrowedBook;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class LibrariesController extends Controller
{
    //
    public function fetchData()
    {
        $school_id = $this->getSchool()->id;
        $today = date('Y-m-d', strtotime('now'));
        $books = LibraryBook::where('school_id', $school_id)->select('*', \DB::raw('SUM(quantity) as total_quantity'))->first();
        $categories = LibraryBookCategory::where('school_id', $school_id)->count();
        $borrowed = LibraryBorrowedBook::where('school_id', $school_id)->where('is_returned', 'no')->count();
        $overdue_borrowed = LibraryBorrowedBook::where('school_id', $school_id)->where('is_returned', 'no')->where('due_date', '<', $today)->count();

        return response()->json(compact('books', 'categories', 'borrowed', 'overdue_borrowed'), 200);
    }
    public function books(Request $request)
    {
        $user = $this->getUser();
        $school_id = $this->getSchool()->id;
        $searchParams = $request->all();
        $bookQuery = LibraryBook::query();
        $limit = 10;
        $keyword = Arr::get($searchParams, 'keyword', '');
        $category = Arr::get($searchParams, 'category_id', '');
        $curriculum_level_group_id = Arr::get($searchParams, 'curriculum_level_group_id', '');
        if (!empty($keyword)) {
            $bookQuery->where(function ($q) use ($keyword) {
                $q->where('ISBN', 'LIKE', '%' . $keyword . '%');
                $q->orWhere('title', 'LIKE', '%' . $keyword . '%');
                $q->orWhere('publisher', 'LIKE', '%' . $keyword . '%');
                $q->orWhere('authors', 'LIKE', '%' . $keyword . '%');
                $q->orWhere('copyright_year', 'LIKE', '%' . $keyword . '%');
            });
        }
        if (!empty($category)) {
            $bookQuery->where('library_book_category_id', $category);
        }
        if (!empty($curriculum_level_group_id)) {
            $bookQuery->where('curriculum_level_group_id', $curriculum_level_group_id);
        }
        if ($user->role === 'student') {
            $student = $this->getStudent();
            $level_id = $student->current_level;
            $level = Level::find($level_id);
            $curriculum_level_group_id = $level->curriculum_level_group_id;
            $bookQuery->where('curriculum_level_group_id', $curriculum_level_group_id);
        }
        $books = $bookQuery->with(['borrowedBooks' => function ($q) {
            $q->where('is_returned', 'no');
        }, 'borrowedBooks.borrower', 'borrowedBooks.processor', 'borrowedBooks.receiver', 'category', 'levelGroup', 'recordedBy'])->where('school_id', $school_id)->orderBy('id', 'DESC')->paginate($limit);

        return response()->json(compact('books'), 200);
    }
    public function storeBook(Request $request)
    {
        $user = $this->getUser();
        $school_id = $this->getSchool()->id;
        $isbn = $request->ISBN;
        $book = LibraryBook::where('ISBN', $isbn)->first();
        if (!$book) {
            $book = new LibraryBook();
        }

        $book->school_id = $school_id;
        $book->curriculum_level_group_id = $request->curriculum_level_group_id;

        $book->library_book_category_id = $request->library_book_category_id;
        $book->ISBN = $isbn;
        $book->title = $request->title;
        $book->authors = $request->authors;
        $book->publisher = $request->publisher;
        $book->copyright_year = $request->copyright_year;
        $book->serial_no = $request->serial_no;

        $book->quantity = $request->quantity;
        $book->description = $request->description;
        $book->created_by = $user->id;
        $book->save();

        return response()->json([], 200);
    }

    public function updateBook(Request $request, LibraryBook $book)
    {
        $isbn = $request->ISBN;
        $book->library_book_category_id = $request->library_book_category_id;
        $book->ISBN = $isbn;
        $book->title = $request->title;
        $book->authors = $request->authors;
        $book->publisher = $request->publisher;
        $book->copyright_year = $request->copyright_year;
        $book->serial_no = $request->serial_no;
        $book->quantity = $request->quantity;
        $book->description = $request->description;
        $book->save();

        return response()->json(compact('book'), 200);
    }
    public function destroyBook(Request $request, LibraryBook $book)
    {
        $book->delete();

        return response()->json([], 204);
    }

    public function bookCategory()
    {
        $school_id = $this->getSchool()->id;
        $user = $this->getUser();
        $bookQuery = LibraryBookCategory::query();
        if ($user->role === 'student') {
            $student = $this->getStudent();
            $level_id = $student->current_level;
            $level = Level::find($level_id);
            $curriculum_level_group_id = $level->curriculum_level_group_id;

            $bookQuery->where('curriculum_level_group_id', $curriculum_level_group_id);
        }
        $book_categories = $bookQuery->with('levelGroup')->where('school_id', $school_id)->orderBy('name')->get();

        return response()->json(compact('book_categories'), 200);
    }
    public function storeBookCategory(Request $request)
    {
        $user = $this->getUser();
        $school_id = $this->getSchool()->id;
        $name = $request->name;
        $book_category = LibraryBookCategory::where('name', $name)->first();
        if (!$book_category) {
            $book_category = new LibraryBookCategory();
        }

        $book_category->school_id = $school_id;
        $book_category->curriculum_level_group_id = $request->curriculum_level_group_id;
        $book_category->name = $request->name;
        $book_category->description = $request->description;
        $book_category->save();

        return response()->json([], 200);
    }

    public function updateBookCategory(Request $request, LibraryBookCategory $category)
    {
        $category->curriculum_level_group_id = $request->curriculum_level_group_id;
        $category->name = $request->name;
        $category->description = $request->description;
        $category->save();

        return response()->json(compact('category'), 200);
    }
    public function destroyBookCategory(Request $request, LibraryBookCategory $category)
    {
        $category->delete();

        return response()->json([], 204);
    }

    public function borrowedBooks()
    {
        $user_id = $this->getUser()->id;
        $school_id = $this->getSchool()->id;
        $borrowed_books = LibraryBorrowedBook::with(['libraryBook.category', 'borrower', 'processor', 'receiver',])->where(['school_id' => $school_id, 'borrower_id' => $user_id])->where('is_returned', 'no')->orderBy('id', 'DESC')->get();

        return response()->json(compact('borrowed_books'), 200);
    }
    public function newBorrowing(Request $request)
    {
        $user = $this->getUser();
        $school_id = $this->getSchool()->id;
        $borrow = new LibraryBorrowedBook();
        $borrow->school_id = $school_id;
        $borrow->library_book_id = $request->library_book_id;
        $borrow->borrower_id = $request->borrower_id;
        $borrow->date_borrowed = $request->date_borrowed;
        $borrow->due_date = $request->due_date;
        $borrow->quantity = $request->quantity;
        $borrow->processed_by = $user->id;
        $borrow->save();

        return $this->borrowedBooks();
    }

    public function returnBook(Request $request, LibraryBorrowedBook $book)
    {
        $user = $this->getUser();
        $book->is_returned = 'yes';
        // $book->quantity_returned = $request->quantity_returned;
        // $book->date_returned = $request->date_returned;
        $book->received_by = $user->id;
        $book->save();

        return $this->borrowedBooks();
    }

    public function delete(Request $request, LibraryBorrowedBook $book)
    {
        $book->delete();

        return response()->json([], 204);
    }
}
