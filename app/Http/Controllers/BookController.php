<?php
namespace App\Http\Controllers;

use App\Models\Book;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function index()
    {
        $books = Book::all();
        return response()->json($books);
    }

    public function createBook(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'image' => 'required|image',
        ]);

        $book = new Book();
        if ($request->hasFile('image')) {
            $originalNameFile = $request->file('image')->getClientOriginalName();
            $newNameFile = Carbon::now()->timestamp . $originalNameFile;
            $pathFiles = './uploads/';

            $request->file('image')->move($pathFiles, $newNameFile);
            $book->name = $request->name;
            $book->image = ltrim($pathFiles, '.') . $newNameFile;
            $book->save();
        }
        return $book;
    }

    public function getBook($book)
    {
        $book = Book::find($book);
        if ($book) {
            return response()->json($book);
        }
        return response()->json(['message' => 'Book not found']);
    }

    public function removeBook($book)
    {
        $message = 'Book not found';
        $book = Book::find($book);
        if ($book) {
            try {
                $pathFile = base_path('public') . $book->image;
                if (file_exists($pathFile)) {
                    unlink($pathFile);
                }
                $book->delete();
                $message = 'Book removed';
            } catch (Exception $e) {
                $message = 'Error 500. Book not deleted';
            }
        }
        return response()->json(['message' => $message]);
    }

    public function updateBook(Request $request, $book)
    {
        $message = 'Book not found';
        $book = Book::find($book);

        if ($book) {

            $book->name = $request->name;
            $message = 'Book updated';

            if ($request->hasFile('image')) {
                //remove old image
                try {
                    $pathFile = base_path('public') . $book->image;
                    if (file_exists($pathFile)) {
                        unlink($pathFile);
                    }
                    $originalNameFile = $request->file('image')->getClientOriginalName();
                    $newNameFile = Carbon::now()->timestamp . $originalNameFile;
                    $pathFiles = './uploads/';
                    $request->file('image')->move($pathFiles, $newNameFile);
                    $book->image = ltrim($pathFiles, '.') . $newNameFile;
                } catch (Exception $e) {
                    $message = 'Error 500. Book not updated';
                }
            }
            $book->save();
        }
        return response()->json(['message' => $message]);
    }
}
