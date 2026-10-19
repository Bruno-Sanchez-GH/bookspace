<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class GoogleBooksService
{
    /**
     * @return list<array<string, mixed>>
     */
    public function search(string $query, int $limit = 12): array
    {
        $response = $this->client()->get('/volumes', [
            'q' => $query,
            'maxResults' => $limit,
            'orderBy' => 'relevance',
            'printType' => 'books',
        ])->throw();

        return collect($response->json('items', []))
            ->map(fn (array $volume): array => $this->normalize($volume))
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function find(string $externalId): array
    {
        $response = $this->client()->get('/volumes/'.rawurlencode($externalId))->throw();

        return $this->normalize($response->json());
    }

    private function client(): PendingRequest
    {
        $apiKey = config('services.google_books.api_key');

        return Http::acceptJson()
            ->baseUrl((string) config('services.google_books.base_url'))
            ->connectTimeout(3)
            ->timeout(8)
            ->retry(2, 200, throw: false)
            ->when(filled($apiKey), fn (PendingRequest $request): PendingRequest => $request->withQueryParameters([
                'key' => $apiKey,
            ]));
    }

    /**
     * @param  array<string, mixed>  $volume
     * @return array<string, mixed>
     */
    private function normalize(array $volume): array
    {
        $information = Arr::get($volume, 'volumeInfo', []);
        $identifiers = collect(Arr::get($information, 'industryIdentifiers', []));
        $isbn13 = $identifiers->firstWhere('type', 'ISBN_13');
        $isbn10 = $identifiers->firstWhere('type', 'ISBN_10');
        $isbn = data_get($isbn13, 'identifier') ?? data_get($isbn10, 'identifier');
        $thumbnail = Arr::get($information, 'imageLinks.thumbnail');

        return [
            'external_id' => (string) Arr::get($volume, 'id'),
            'source' => 'google_books',
            'title' => (string) Arr::get($information, 'title', 'Título no disponible'),
            'authors' => array_values(Arr::get($information, 'authors', ['Autor desconocido'])),
            'description' => filled(Arr::get($information, 'description'))
                ? strip_tags((string) Arr::get($information, 'description'))
                : null,
            'published_date' => Arr::get($information, 'publishedDate'),
            'isbn' => $isbn,
            'categories' => array_values(Arr::get($information, 'categories', [])),
            'page_count' => Arr::get($information, 'pageCount'),
            'thumbnail_url' => is_string($thumbnail) ? str_replace('http://', 'https://', $thumbnail) : null,
            'info_url' => Arr::get($information, 'infoLink'),
        ];
    }
}
