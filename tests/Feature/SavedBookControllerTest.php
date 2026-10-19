<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Http;
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
            'user_id' => $user->id,
        ]);
        Http::assertSentCount(1);
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
