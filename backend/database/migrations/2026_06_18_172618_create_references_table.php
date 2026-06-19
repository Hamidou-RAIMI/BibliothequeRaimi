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
        Schema::create('references', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->string('subtitle', 255)->nullable();
            $table->text('abstract')->nullable();
            $table->string('isbn', 50)->nullable();
            $table->integer('publication_year')->nullable();
            $table->enum('language', ['fr', 'en', 'autre'])->default('fr');
            $table->enum('document_type', ['livre', 'memoire', 'these', 'article', 'revue', 'rapport', 'guide', 'autre'])->notNull();
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->foreignId('publisher_id')->nullable()->constrained('publishers')->nullOnDelete();
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');
            $table->string('cover_image', 255)->nullable();
            $table->string('file_path', 255)->nullable();
            $table->integer('pages')->nullable();
            $table->integer('download_count')->default(0);
            $table->integer('view_count')->default(0);
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('references');
    }
};
