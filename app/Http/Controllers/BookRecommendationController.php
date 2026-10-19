<?php

namespace App\Http\Controllers;

use App\Exceptions\RecommendationException;
use App\Services\BookRecommendationService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BookRecommendationController extends Controller
{
    public function store(Request $request, BookRecommendationService $recommendations): RedirectResponse
    {
        try {
            $result = $recommendations->recommendationsFor($request->user());
        } catch (ConnectionException|RequestException) {
            return back()->withErrors([
                'service' => 'Google Books no está disponible en este momento. Intentá nuevamente en unos minutos.',
            ], 'recommendations');
        } catch (RecommendationException $exception) {
            return back()->withErrors([
                'service' => $exception->getMessage(),
            ], 'recommendations');
        }

        return to_route('library.index')->with('bookRecommendations', $result);
    }
}
