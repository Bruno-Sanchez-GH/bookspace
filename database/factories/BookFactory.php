<?php

namespace Database\Factories;

use App\Models\Book;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Book>
 */
class BookFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'external_id' => fake()->unique()->bothify('????????????'),
            'source' => 'google_books',
            'title' => fake()->sentence(3),
            'authors' => [fake()->name()],
            'description' => fake()->paragraph(),
            'published_date' => (string) fake()->year(),
            'isbn' => fake()->unique()->isbn13(),
            'categories' => [fake()->word()],
            'page_count' => fake()->numberBetween(80, 800),
            'thumbnail_url' => 'https://example.com/cover.jpg',
            'info_url' => 'https://example.com/book',
        ];
    }
}
