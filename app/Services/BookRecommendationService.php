<?php

namespace App\Services;

use App\Exceptions\RecommendationException;
use App\Models\Book;
use App\Models\User;
use App\ReadingStatus;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class BookRecommendationService
{
    public function __construct(
        private GoogleBooksService $googleBooks,
        private GeminiRecommendationService $gemini,
    ) {}

    /**
     * @return array{summary: string, books: list<array<string, mixed>>}
     */
    public function recommendationsFor(User $user): array
    {
        if (! $this->gemini->isConfigured()) {
            throw new RecommendationException('Gemini todavía no está configurado en el servidor.');
        }

        /** @var Collection<int, Book> $library */
        $library = $user->savedBooks()
            ->latest('book_user.updated_at')
            ->limit(20)
            ->get();

        if ($library->isEmpty()) {
            throw new RecommendationException('Guardá al menos un libro antes de pedir recomendaciones.');
        }

        $savedExternalIds = $library->pluck('external_id')->all();
        $candidates = collect($this->googleBooks->search($this->searchQuery($library), 12))
            ->filter(fn (array $book): bool => filled($book['external_id'] ?? null)
                && ! in_array($book['external_id'], $savedExternalIds, true))
            ->unique('external_id')
            ->take(8)
            ->values()
            ->all();

        if ($candidates === []) {
            throw new RecommendationException('No encontramos libros nuevos para recomendarte en este momento.');
        }

        return $this->gemini->recommend(
            $library->map(fn (Book $book): array => $this->libraryContext($book))->all(),
            $candidates,
        );
    }

    /**
     * @param  Collection<int, Book>  $library
     */
    private function searchQuery(Collection $library): string
    {
        $category = $library
            ->flatMap(fn (Book $book): array => $book->categories ?? [])
            ->filter(fn (mixed $value): bool => is_string($value) && filled(trim($value)))
            ->map(fn (string $value): string => trim($value))
            ->countBy()
            ->sortDesc()
            ->keys()
            ->first();

        if (is_string($category)) {
            return 'subject:"'.Str::replace('"', '', $category).'"';
        }

        $author = $library
            ->flatMap(fn (Book $book): array => $book->authors ?? [])
            ->filter(fn (mixed $value): bool => is_string($value)
                && filled(trim($value))
                && $value !== 'Autor desconocido')
            ->map(fn (string $value): string => trim($value))
            ->countBy()
            ->sortDesc()
            ->keys()
            ->first();

        if (is_string($author)) {
            return 'inauthor:"'.Str::replace('"', '', $author).'"';
        }

        return 'subject:fiction';
    }

    /**
     * @return array<string, mixed>
     */
    private function libraryContext(Book $book): array
    {
        $pageCount = $book->page_count;
        $currentPage = (int) $book->pivot->current_page;
        $progressPercentage = $pageCount
            ? min((int) round(($currentPage / $pageCount) * 100), 100)
            : null;

        return [
            'title' => $book->title,
            'authors' => $book->authors,
            'categories' => $book->categories,
            'reading_status' => ReadingStatus::tryFrom($book->pivot->reading_status)?->label(),
            'progress_percentage' => $progressPercentage,
        ];
    }
}
