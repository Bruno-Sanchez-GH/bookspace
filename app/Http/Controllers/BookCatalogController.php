<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchBooksRequest;
use App\Services\GoogleBooksService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\View\View;

class BookCatalogController extends Controller
{
    public function index(SearchBooksRequest $request, GoogleBooksService $googleBooks): View
    {
        $query = $request->validated('q', 'subject:fiction');
        $books = [];
        $serviceError = null;

        try {
            $books = $googleBooks->search($query);
        } catch (ConnectionException|RequestException $exception) {
            report($exception);
            $serviceError = 'Google Books no está disponible en este momento. Intentá nuevamente en unos minutos.';
        }

        $savedExternalIds = $request->user()?->savedBooks()->pluck('external_id')->all() ?? [];

        return view('catalog.index', [
            'books' => $books,
            'query' => $request->string('q')->toString(),
            'savedExternalIds' => $savedExternalIds,
            'serviceError' => $serviceError,
        ]);
    }
}
