<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use App\ReadingStatus;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BookRecommendationControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guest_cannot_generate_recommendations(): void
    {
        Http::preventStrayRequests();

        $response = $this->post(route('recommendations.store'));

        $response->assertRedirect(route('login'));
        Http::assertNothingSent();
    }

    public function test_user_needs_a_saved_book_to_generate_recommendations(): void
    {
        config(['services.gemini.api_key' => 'test-gemini-key']);
        $user = User::factory()->create();
        Http::preventStrayRequests();

        $response = $this->actingAs($user)->post(route('recommendations.store'));

        $response->assertRedirect();
        $response->assertSessionHasErrors([
            'service' => 'Guardá al menos un libro antes de pedir recomendaciones.',
        ], null, 'recommendations');
        Http::assertNothingSent();
    }

    public function test_missing_gemini_configuration_returns_a_controlled_error(): void
    {
        config(['services.gemini.api_key' => null]);
        $user = User::factory()->create();
        $savedBook = Book::factory()->create();
        $user->savedBooks()->attach($savedBook);
        Http::preventStrayRequests();

        $response = $this->actingAs($user)->post(route('recommendations.store'));

        $response->assertRedirect();
        $response->assertSessionHasErrors([
            'service' => 'Gemini todavía no está configurado en el servidor.',
        ], null, 'recommendations');
        Http::assertNothingSent();
    }

    public function test_user_receives_recommendations_from_real_google_books_candidates(): void
    {
        config([
            'services.gemini.api_key' => 'test-gemini-key',
            'services.gemini.model' => 'gemini-3.8-flash',
        ]);
        $user = User::factory()->create(['email' => 'private@example.com']);
        $savedBook = Book::factory()->create([
            'external_id' => 'dune-volume',
            'title' => 'Dune',
            'authors' => ['Frank Herbert'],
            'categories' => ['Science Fiction'],
            'page_count' => 400,
        ]);
        $user->savedBooks()->attach($savedBook, [
            'reading_status' => ReadingStatus::Completed->value,
            'current_page' => 400,
        ]);
        Http::preventStrayRequests();
        Http::fake([
            'www.googleapis.com/books/v1/volumes*' => Http::response($this->googleBooksResponse()),
            'generativelanguage.googleapis.com/v1beta/interactions' => Http::response(
                $this->geminiResponse([
                    'summary' => 'Elegí ciencia ficción con mundos complejos.',
                    'recommendations' => [[
                        'external_id' => 'hyperion-volume',
                        'reason' => 'Mantiene la escala épica y suma una estructura narrativa diferente.',
                    ]],
                ]),
            ),
        ]);

        $response = $this->actingAs($user)->post(route('recommendations.store'));

        $response->assertRedirect(route('library.index'));
        $response->assertSessionHas('bookRecommendations.summary', 'Elegí ciencia ficción con mundos complejos.');
        $response->assertSessionHas('bookRecommendations.books.0.title', 'Hyperion');
        $response->assertSessionHas('bookRecommendations.books.0.external_id', 'hyperion-volume');
        Http::assertSent(function (Request $request): bool {
            return str_starts_with($request->url(), 'https://www.googleapis.com/books/v1/volumes?')
                && str_contains(urldecode($request->url()), 'q=Science Fiction')
                && str_contains($request->url(), 'maxResults=40');
        });
        Http::assertSent(function (Request $request): bool {
            $payload = $request->data();

            return $request->url() === 'https://generativelanguage.googleapis.com/v1beta/interactions'
                && $request->header('x-goog-api-key')[0] === 'test-gemini-key'
                && $payload['model'] === 'gemini-3.8-flash'
                && $payload['response_format']['mime_type'] === 'application/json'
                && $payload['response_format']['schema']['properties']['recommendations']['items']['properties']['external_id']['enum'] === [
                    'hyperion-volume',
                    'foundation-volume',
                ]
                && str_contains($payload['input'], 'Dune')
                && ! str_contains($payload['input'], 'private@example.com');
        });
        Http::assertSentCount(2);
    }

    public function test_recommendations_fall_back_to_an_author_when_a_category_has_no_new_books(): void
    {
        config(['services.gemini.api_key' => 'test-gemini-key']);
        $user = User::factory()->create();
        $savedBook = Book::factory()->create([
            'external_id' => 'dune-volume',
            'title' => 'Dune',
            'authors' => ['Frank Herbert'],
            'categories' => ['Science Fiction'],
        ]);
        $user->savedBooks()->attach($savedBook);
        Http::preventStrayRequests();
        Http::fake([
            'www.googleapis.com/books/v1/volumes*' => Http::sequence()
                ->push([
                    'items' => [[
                        'id' => 'dune-volume',
                        'volumeInfo' => [
                            'title' => 'Dune',
                            'authors' => ['Frank Herbert'],
                            'categories' => ['Science Fiction'],
                        ],
                    ]],
                ])
                ->push([
                    'items' => [[
                        'id' => 'dune-messiah-volume',
                        'volumeInfo' => [
                            'title' => 'Dune Messiah',
                            'authors' => ['Frank Herbert'],
                            'categories' => ['Science Fiction'],
                        ],
                    ]],
                ]),
            'generativelanguage.googleapis.com/v1beta/interactions' => Http::response(
                $this->geminiResponse([
                    'summary' => 'Continuá explorando el universo de Dune.',
                    'recommendations' => [[
                        'external_id' => 'dune-messiah-volume',
                        'reason' => 'Continúa la historia y coincide con tus intereses.',
                    ]],
                ]),
            ),
        ]);

        $response = $this->actingAs($user)->post(route('recommendations.store'));

        $response->assertRedirect(route('library.index'));
        $response->assertSessionHas('bookRecommendations.books.0.title', 'Dune Messiah');
        Http::assertSent(fn (Request $request): bool => str_contains(
            urldecode($request->url()),
            'q=Science Fiction',
        ));
        Http::assertSent(fn (Request $request): bool => str_contains(
            urldecode($request->url()),
            'q=Frank Herbert',
        ));
        Http::assertSentCount(3);
    }

    public function test_recommendations_exclude_saved_books_outside_the_twenty_book_context(): void
    {
        config(['services.gemini.api_key' => 'test-gemini-key']);
        $user = User::factory()->create();
        $olderSavedBook = Book::factory()->create([
            'external_id' => 'older-saved-volume',
            'categories' => ['Juvenile Fiction'],
        ]);
        $user->savedBooks()->attach($olderSavedBook, [
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);
        $recentSavedBooks = Book::factory()
            ->count(20)
            ->create(['categories' => ['Juvenile Fiction']]);
        $user->savedBooks()->attach($recentSavedBooks, [
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        Http::preventStrayRequests();
        Http::fake([
            'www.googleapis.com/books/v1/volumes*' => Http::response([
                'items' => [
                    [
                        'id' => 'older-saved-volume',
                        'volumeInfo' => [
                            'title' => 'Already Saved',
                            'categories' => ['Juvenile Fiction'],
                        ],
                    ],
                    [
                        'id' => 'new-volume',
                        'volumeInfo' => [
                            'title' => 'A New Recommendation',
                            'categories' => ['Juvenile Fiction'],
                        ],
                    ],
                ],
            ]),
            'generativelanguage.googleapis.com/v1beta/interactions' => Http::response(
                $this->geminiResponse([
                    'summary' => 'Una opción que todavía no guardaste.',
                    'recommendations' => [[
                        'external_id' => 'new-volume',
                        'reason' => 'Coincide con tus intereses sin repetir tu biblioteca.',
                    ]],
                ]),
            ),
        ]);

        $response = $this->actingAs($user)->post(route('recommendations.store'));

        $response->assertRedirect(route('library.index'));
        $response->assertSessionHas('bookRecommendations.books.0.external_id', 'new-volume');
        Http::assertSent(function (Request $request): bool {
            if ($request->url() !== 'https://generativelanguage.googleapis.com/v1beta/interactions') {
                return false;
            }

            return $request->data()['response_format']['schema']['properties']['recommendations']['items']['properties']['external_id']['enum'] === [
                'new-volume',
            ];
        });
        Http::assertSent(fn (Request $request): bool => str_contains($request->url(), 'maxResults=40'));
        Http::assertSentCount(2);
    }

    public function test_recommendations_escape_text_returned_by_gemini(): void
    {
        config(['services.gemini.api_key' => 'test-gemini-key']);
        $user = User::factory()->create();
        $savedBook = Book::factory()->create(['categories' => ['Science Fiction']]);
        $user->savedBooks()->attach($savedBook);
        Http::preventStrayRequests();
        Http::fake([
            'www.googleapis.com/books/v1/volumes*' => Http::response($this->googleBooksResponse()),
            'generativelanguage.googleapis.com/v1beta/interactions' => Http::response(
                $this->geminiResponse([
                    'summary' => 'Una selección personalizada.',
                    'recommendations' => [[
                        'external_id' => 'hyperion-volume',
                        'reason' => '<script>alert("xss")</script>',
                    ]],
                ]),
            ),
        ]);

        $response = $this->actingAs($user)
            ->followingRedirects()
            ->post(route('recommendations.store'));

        $response->assertOk();
        $response->assertSee('&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;', false);
        $response->assertDontSee('<script>alert("xss")</script>', false);
    }

    public function test_invalid_gemini_candidate_is_rejected(): void
    {
        config(['services.gemini.api_key' => 'test-gemini-key']);
        $user = User::factory()->create();
        $savedBook = Book::factory()->create(['categories' => ['Science Fiction']]);
        $user->savedBooks()->attach($savedBook);
        Http::preventStrayRequests();
        Http::fake([
            'www.googleapis.com/books/v1/volumes*' => Http::response($this->googleBooksResponse()),
            'generativelanguage.googleapis.com/v1beta/interactions' => Http::response(
                $this->geminiResponse([
                    'summary' => 'Una selección personalizada.',
                    'recommendations' => [[
                        'external_id' => 'invented-volume',
                        'reason' => 'Este identificador no provino de Google Books.',
                    ]],
                ]),
            ),
        ]);

        $response = $this->actingAs($user)->post(route('recommendations.store'));

        $response->assertRedirect();
        $response->assertSessionHasErrors([
            'service' => 'Gemini devolvió recomendaciones inválidas. Intentá nuevamente.',
        ], null, 'recommendations');
    }

    public function test_gemini_failure_returns_to_library_with_a_controlled_error(): void
    {
        config(['services.gemini.api_key' => 'test-gemini-key']);
        $user = User::factory()->create();
        $savedBook = Book::factory()->create(['categories' => ['Science Fiction']]);
        $user->savedBooks()->attach($savedBook);
        Http::preventStrayRequests();
        Http::fake([
            'www.googleapis.com/books/v1/volumes*' => Http::response($this->googleBooksResponse()),
            'generativelanguage.googleapis.com/v1beta/interactions' => Http::response([], 503),
        ]);

        $response = $this->actingAs($user)->post(route('recommendations.store'));

        $response->assertRedirect();
        $response->assertSessionHasErrors([
            'service' => 'Gemini no pudo generar recomendaciones en este momento.',
        ], null, 'recommendations');
        Http::assertSentCount(2);
    }

    public function test_recommendation_requests_are_rate_limited_per_user(): void
    {
        config(['services.gemini.api_key' => 'test-gemini-key']);
        $user = User::factory()->create();
        $savedBook = Book::factory()->create(['categories' => ['Science Fiction']]);
        $user->savedBooks()->attach($savedBook);
        Http::preventStrayRequests();
        Http::fake([
            'www.googleapis.com/books/v1/volumes*' => Http::response($this->googleBooksResponse()),
            'generativelanguage.googleapis.com/v1beta/interactions' => Http::response(
                $this->geminiResponse([
                    'summary' => 'Una selección personalizada.',
                    'recommendations' => [[
                        'external_id' => 'hyperion-volume',
                        'reason' => 'Una opción cercana a tus intereses.',
                    ]],
                ]),
            ),
        ]);

        $this->actingAs($user)->post(route('recommendations.store'))->assertRedirect(route('library.index'));
        $this->post(route('recommendations.store'))->assertRedirect(route('library.index'));
        $this->post(route('recommendations.store'))->assertRedirect(route('library.index'));
        $response = $this->post(route('recommendations.store'));

        $response->assertTooManyRequests();
        Http::assertSentCount(6);
    }

    /**
     * @return array<string, mixed>
     */
    private function googleBooksResponse(): array
    {
        return [
            'items' => [
                [
                    'id' => 'dune-volume',
                    'volumeInfo' => [
                        'title' => 'Dune',
                        'authors' => ['Frank Herbert'],
                        'categories' => ['Science Fiction'],
                    ],
                ],
                [
                    'id' => 'hyperion-volume',
                    'volumeInfo' => [
                        'title' => 'Hyperion',
                        'authors' => ['Dan Simmons'],
                        'publishedDate' => '1989',
                        'categories' => ['Science Fiction'],
                        'imageLinks' => ['thumbnail' => 'http://example.com/hyperion.jpg'],
                    ],
                ],
                [
                    'id' => 'foundation-volume',
                    'volumeInfo' => [
                        'title' => 'Foundation',
                        'authors' => ['Isaac Asimov'],
                        'publishedDate' => '1951',
                        'categories' => ['Science Fiction'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $output
     * @return array<string, mixed>
     */
    private function geminiResponse(array $output): array
    {
        return [
            'status' => 'completed',
            'steps' => [[
                'type' => 'model_output',
                'content' => [[
                    'type' => 'text',
                    'text' => json_encode($output, JSON_THROW_ON_ERROR),
                ]],
            ]],
        ];
    }
}
