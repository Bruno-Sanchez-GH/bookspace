<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateReadingProgressRequest;
use App\Models\Book;
use App\ReadingStatus;
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

        $progressPercentages = $books->mapWithKeys(function (Book $book): array {
            if ($book->page_count === null || $book->page_count === 0) {
                return [$book->getKey() => null];
            }

            $percentage = (int) round(((int) $book->pivot->current_page / $book->page_count) * 100);

            return [$book->getKey() => min($percentage, 100)];
        });

        return view('library.index', [
            'books' => $books,
            'progressPercentages' => $progressPercentages,
            'readingStatuses' => ReadingStatus::cases(),
        ]);
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

    public function update(UpdateReadingProgressRequest $request, Book $book): RedirectResponse
    {
        $request->user()->savedBooks()->updateExistingPivot(
            $book->getKey(),
            $request->safe()->only(['reading_status', 'current_page']),
        );

        return back()->with('status', 'Seguimiento de lectura actualizado.');
    }

    public function destroy(Request $request, Book $book): RedirectResponse
    {
        $request->user()->savedBooks()->detach($book->id);

        return back()->with('status', 'Libro eliminado de tu biblioteca.');
    }
}
