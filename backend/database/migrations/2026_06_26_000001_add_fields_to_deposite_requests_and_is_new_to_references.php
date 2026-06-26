<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deposite_requests', function (Blueprint $table) {
            $table->string('subtitle', 255)->nullable();
            $table->text('abstract')->nullable();
            $table->string('isbn', 50)->nullable();
            $table->integer('publication_year')->nullable();
            $table->enum('language', ['fr', 'en', 'autre'])->default('fr');
            $table->enum('document_type', ['livre', 'memoire', 'these', 'article', 'revue', 'rapport', 'guide', 'autre'])->default('livre');
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('publisher_id')->nullable()->constrained('publishers')->nullOnDelete();
            $table->integer('pages')->nullable();
        });

        Schema::table('references', function (Blueprint $table) {
            $table->boolean('is_new')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('deposite_requests', function (Blueprint $table) {
            $table->dropColumn([
                'subtitle', 'abstract', 'isbn', 'publication_year', 
                'language', 'document_type', 'category_id', 'publisher_id', 'pages'
            ]);
        });

        Schema::table('references', function (Blueprint $table) {
            $table->dropColumn('is_new');
        });
    }
};
