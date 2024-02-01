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
            $table->foreignId('author_id')->constrained('authors');
            $table->foreignId('publisher_id')->constrained('publishers');
            $table->foreignId('genre_id')->constrained('genres');
            $table->string('title')->index();
            $table->string('cover_image')->nullable();
            $table->string('isbn')->unique();
            $table->bigInteger('price');
            $table->longText('description')->nullable();
            $table->bigInteger('stock');
            $table->boolean('available')->default(false);
            $table->date('published');
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
