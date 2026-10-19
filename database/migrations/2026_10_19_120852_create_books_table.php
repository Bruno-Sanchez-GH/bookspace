<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('external_id')->unique();
            $table->string('source')->default('google_books');
            $table->string('title');
            $table->json('authors');
            $table->text('description')->nullable();
            $table->string('published_date')->nullable();
            $table->string('isbn', 32)->nullable()->index();
            $table->json('categories')->nullable();
            $table->unsignedInteger('page_count')->nullable();
            $table->text('thumbnail_url')->nullable();
            $table->text('info_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
