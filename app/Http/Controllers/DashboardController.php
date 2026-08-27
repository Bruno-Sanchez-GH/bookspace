<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $stats = [
            [
                'title' => 'Libros guardados',
                'value' => 1248,
                'description' => 'Títulos agregados por usuarios a sus bibliotecas personales.',
                'icon' => 'bookmark',
            ],
            [
                'title' => 'Usuarios activos',
                'value' => 386,
                'description' => 'Usuarios simulados con actividad reciente en la plataforma.',
                'icon' => 'users',
            ],
            [
                'title' => 'Búsquedas realizadas',
                'value' => 3542,
                'description' => 'Consultas simuladas por título, autor, categoría o ISBN.',
                'icon' => 'search',
            ],
            [
                'title' => 'Libros consultados desde Google Books',
                'value' => 5720,
                'description' => 'Resultados bibliográficos preparados para futura integración real.',
                'icon' => 'globe',
            ],
        ];

        $charts = [
            'searchesByMonth' => [
                'labels' => ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago'],
                'values' => [240, 310, 420, 390, 460, 520, 610, 592],
            ],
            'savedByCategory' => [
                'labels' => ['Fantasía', 'Ciencia ficción', 'Historia', 'Romance', 'Tecnología'],
                'values' => [320, 265, 228, 190, 245],
            ],
            'userActivity' => [
                'labels' => ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago'],
                'values' => [110, 145, 168, 172, 210, 244, 286, 301],
            ],
            'consultedDistribution' => [
                'labels' => ['Fantasía', 'Ciencia ficción', 'Historia', 'Romance', 'Tecnología'],
                'values' => [28, 22, 18, 14, 18],
            ],
        ];

        return view('dashboard', [
            'stats' => $stats,
            'charts' => $charts,
        ]);
    }
}

