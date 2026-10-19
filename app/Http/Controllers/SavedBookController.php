<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Services\GoogleBooksService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SavedBookController extends Controller
{
    public function index(Request $request): View
    {
        $books = $request->user()
            ->savedBooks()
            ->latest('book_user.created_at')
            ->get();

        return view('library.index', ['books' => $books]);
    }

    public function store(
        Request $request,
        string $externalId,
        GoogleBooksService $googleBooks,
    ): RedirectResponse {
        try {
            $bookData = $googleBooks->find($externalId);
        } catch (ConnectionException|RequestException $exception) {
            report($exception);

            return back()->withErrors([
                'catalog' => 'No pudimos guardar el libro porque Google Books no está disponible.',
            ]);
        }

        $book = Book::updateOrCreate(
            ['external_id' => $bookData['external_id']],
            $bookData,
        );

        $request->user()->savedBooks()->syncWithoutDetaching([$book->id]);

        return back()->with('status', 'Libro guardado en tu biblioteca.');
    }

    public function destroy(Request $request, Book $book): RedirectResponse
    {
        $request->user()->savedBooks()->detach($book->id);

        return back()->with('status', 'Libro eliminado de tu biblioteca.');
    }
}
