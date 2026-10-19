<?php

namespace Tests\Feature;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BookCatalogControllerTest extends TestCase
{
    public function test_catalog_renders_normalized_google_books_results(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'www.googleapis.com/books/v1/volumes*' => Http::response([
                'items' => [[
                    'id' => 'dune-volume',
                    'volumeInfo' => [
                        'title' => 'Dune',
                        'authors' => ['Frank Herbert'],
                        'publishedDate' => '1965',
                        'categories' => ['Fiction'],
                        'imageLinks' => ['thumbnail' => 'http://example.com/dune.jpg'],
                    ],
                ]],
            ]),
        ]);

        $response = $this->get(route('catalog.index', ['q' => 'Dune']));

        $response->assertOk();
        $response->assertSee('Dune');
        $response->assertSee('Frank Herbert');
        $response->assertSee('https://example.com/dune.jpg');
        Http::assertSent(fn (Request $request): bool => str_starts_with(
            $request->url(),
            'https://www.googleapis.com/books/v1/volumes?',
        ) && str_contains($request->url(), 'q=Dune')
            && str_contains($request->url(), 'maxResults=12'));
    }

    public function test_catalog_validates_short_search_query(): void
    {
        Http::preventStrayRequests();

        $response = $this->from(route('catalog.index'))->get(route('catalog.index', ['q' => 'a']));

        $response->assertRedirect(route('catalog.index'));
        $response->assertSessionHasErrors('q');
        Http::assertNothingSent();
    }

    public function test_catalog_displays_service_error_when_google_books_fails(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'www.googleapis.com/books/v1/volumes*' => Http::response([], 503),
        ]);

        $response = $this->get(route('catalog.index', ['q' => 'Dune']));

        $response->assertOk();
        $response->assertSee('Google Books no está disponible');
        Http::assertSentCount(2);
    }
}
