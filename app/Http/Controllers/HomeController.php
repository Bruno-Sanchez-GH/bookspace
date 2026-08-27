<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $featuredBooks = [
            [
                'title' => 'Cien años de soledad',
                'author' => 'Gabriel García Márquez',
                'category' => 'Realismo mágico',
                'year' => '1967',
                'cover' => 'https://books.google.com/books/content?id=kmAQCwAAQBAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api',
            ],
            [
                'title' => 'Sapiens',
                'author' => 'Yuval Noah Harari',
                'category' => 'Historia',
                'year' => '2011',
                'cover' => 'https://books.google.com/books/content?id=zfuOEAAAQBAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api',
            ],
            [
                'title' => 'Dune',
                'author' => 'Frank Herbert',
                'category' => 'Ciencia ficción',
                'year' => '1965',
                'cover' => 'https://books.google.com/books/content?id=daWMEAAAQBAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api',
            ],
            [
                'title' => 'Atomic Habits',
                'author' => 'James Clear',
                'category' => 'Desarrollo personal',
                'year' => '2018',
                'cover' => 'https://covers.openlibrary.org/b/isbn/9780735211292-M.jpg?default=false',
            ],
        ];

        return view('home', [
            'featuredBooks' => $featuredBooks,
        ]);
    }
}

