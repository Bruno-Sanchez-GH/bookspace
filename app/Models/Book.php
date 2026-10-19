<?php

namespace App\Models;

use Database\Factories\BookFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable([
    'external_id',
    'source',
    'title',
    'authors',
    'description',
    'published_date',
    'isbn',
    'categories',
    'page_count',
    'thumbnail_url',
    'info_url',
])]
class Book extends Model
{
    /** @use HasFactory<BookFactory> */
    use HasFactory;

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'authors' => 'array',
            'categories' => 'array',
            'page_count' => 'integer',
        ];
    }
}
