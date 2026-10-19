<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use App\ReadingStatus;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SavedBookControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guest_cannot_save_book(): void
    {
        $response = $this->post(route('library.store', 'dune-volume'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_saves_google_book_with_eloquent(): void
    {
        $user = User::factory()->create();
        Http::preventStrayRequests();
        Http::fake([
            'www.googleapis.com/books/v1/volumes/dune-volume*' => Http::response([
                'id' => 'dune-volume',
                'volumeInfo' => [
                    'title' => 'Dune',
                    'authors' => ['Frank Herbert'],
                    'description' => '<b>Una novela</b> de ciencia ficción.',
                    'publishedDate' => '1965',
                    'industryIdentifiers' => [
                        ['type' => 'ISBN_13', 'identifier' => '9780441172719'],
                    ],
                    'categories' => ['Science Fiction'],
                    'pageCount' => 412,
                    'imageLinks' => ['thumbnail' => 'http://example.com/dune.jpg'],
                    'infoLink' => 'https://books.google.com/dune',
                ],
            ]),
        ]);

        $response = $this->actingAs($user)->post(route('library.store', 'dune-volume'));

        $response->assertRedirect();
        $response->assertSessionHas('status', 'Libro guardado en tu biblioteca.');
        $book = Book::query()->where('external_id', 'dune-volume')->firstOrFail();
        $this->assertSame('Una novela de ciencia ficción.', $book->description);
        $this->assertSame(['Frank Herbert'], $book->authors);
        $this->assertDatabaseHas('book_user', [
            'book_id' => $book->id,
            'current_page' => 0,
            'reading_status' => ReadingStatus::Pending->value,
            'user_id' => $user->id,
        ]);
        Http::assertSentCount(1);
    }

    public function test_guest_cannot_update_reading_progress(): void
    {
        $book = Book::factory()->create();

        $response = $this->patch(route('library.update', $book), [
            'reading_status' => ReadingStatus::Reading->value,
            'current_page' => 50,
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_updates_reading_progress(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['page_count' => 400]);
        $user->savedBooks()->attach($book);

        $response = $this->actingAs($user)->patch(route('library.update', $book), [
            'reading_status' => ReadingStatus::Reading->value,
            'current_page' => 100,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status', 'Seguimiento de lectura actualizado.');
        $this->assertDatabaseHas('book_user', [
            'book_id' => $book->id,
            'current_page' => 100,
            'reading_status' => ReadingStatus::Reading->value,
            'user_id' => $user->id,
        ]);
    }

    public function test_current_page_cannot_exceed_book_page_count(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['page_count' => 120]);
        $user->savedBooks()->attach($book);

        $response = $this->actingAs($user)->patch(route('library.update', $book), [
            'reading_status' => ReadingStatus::Reading->value,
            'current_page' => 121,
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors([
            'current_page' => 'La página actual no puede superar las 120 páginas del libro.',
        ]);
        $this->assertDatabaseHas('book_user', [
            'book_id' => $book->id,
            'current_page' => 0,
            'reading_status' => ReadingStatus::Pending->value,
            'user_id' => $user->id,
        ]);
    }

    public function test_reading_status_must_be_valid(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $user->savedBooks()->attach($book);

        $response = $this->actingAs($user)->patch(route('library.update', $book), [
            'reading_status' => 'paused',
            'current_page' => 10,
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors([
            'reading_status' => 'El estado de lectura seleccionado no es válido.',
        ]);
        $this->assertDatabaseHas('book_user', [
            'book_id' => $book->id,
            'current_page' => 0,
            'reading_status' => ReadingStatus::Pending->value,
            'user_id' => $user->id,
        ]);
    }

    public function test_reading_progress_requires_status_and_current_page(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $user->savedBooks()->attach($book);

        $response = $this->actingAs($user)->patch(route('library.update', $book));

        $response->assertRedirect();
        $response->assertSessionHasErrors([
            'current_page' => 'Ingresá la página actual.',
            'reading_status' => 'Seleccioná un estado de lectura.',
        ]);
    }

    #[DataProvider('invalidCurrentPages')]
    public function test_current_page_must_be_a_valid_unsigned_integer(mixed $currentPage, string $message): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['page_count' => null]);
        $user->savedBooks()->attach($book);

        $response = $this->actingAs($user)->patch(route('library.update', $book), [
            'reading_status' => ReadingStatus::Reading->value,
            'current_page' => $currentPage,
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['current_page' => $message]);
        $this->assertDatabaseHas('book_user', [
            'book_id' => $book->id,
            'current_page' => 0,
            'reading_status' => ReadingStatus::Pending->value,
            'user_id' => $user->id,
        ]);
    }

    /**
     * @return array<string, array{mixed, string}>
     */
    public static function invalidCurrentPages(): array
    {
        return [
            'not an integer' => ['ten', 'La página actual debe ser un número entero.'],
            'negative integer' => [-1, 'La página actual no puede ser negativa.'],
            'larger than database column' => [4294967296, 'La página actual es demasiado grande.'],
        ];
    }

    public function test_user_cannot_update_another_users_library_entry(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $book = Book::factory()->create();
        $owner->savedBooks()->attach($book);

        $response = $this->actingAs($otherUser)->patch(route('library.update', $book), [
            'reading_status' => ReadingStatus::Completed->value,
            'current_page' => $book->page_count,
        ]);

        $response->assertForbidden();
        $this->assertDatabaseHas('book_user', [
            'book_id' => $book->id,
            'current_page' => 0,
            'reading_status' => ReadingStatus::Pending->value,
            'user_id' => $owner->id,
        ]);
    }

    public function test_library_displays_status_and_calculated_progress(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create([
            'page_count' => 400,
            'title' => 'Libro en progreso',
        ]);
        $user->savedBooks()->attach($book, [
            'reading_status' => ReadingStatus::Reading->value,
            'current_page' => 100,
        ]);

        $response = $this->actingAs($user)->get(route('library.index'));

        $response->assertOk();
        $response->assertSee('Libro en progreso');
        $response->assertSee('Leyendo');
        $response->assertSee('100 de 400 páginas');
        $response->assertSee('25%');
    }

    public function test_library_displays_current_page_when_total_page_count_is_unknown(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create([
            'page_count' => null,
            'title' => 'Libro sin total',
        ]);
        $user->savedBooks()->attach($book, [
            'reading_status' => ReadingStatus::Reading->value,
            'current_page' => 35,
        ]);

        $response = $this->actingAs($user)->get(route('library.index'));

        $response->assertOk();
        $response->assertSee('35 páginas leídas');
        $response->assertDontSee('Avance de Libro sin total');
    }

    public function test_authenticated_user_can_remove_saved_book(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $user->savedBooks()->attach($book);

        $response = $this->actingAs($user)->delete(route('library.destroy', $book));

        $response->assertRedirect();
        $response->assertSessionHas('status', 'Libro eliminado de tu biblioteca.');
        $this->assertDatabaseMissing('book_user', [
            'book_id' => $book->id,
            'user_id' => $user->id,
        ]);
        $this->assertModelExists($book);
    }
}
