<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kb_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon')->default('BookOpen');
            $table->string('color')->default('blue');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });

        Schema::create('kb_articles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('category_id')->constrained('kb_categories')->cascadeOnDelete();
            $table->unsignedBigInteger('server_id')->nullable()->index();
            $table->unsignedBigInteger('author_id')->nullable()->index();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('content');
            $table->string('status', 20)->default('published'); // draft, published, archived
            $table->boolean('is_pinned')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->integer('sort_order')->default(0);
            $table->unsignedBigInteger('views_count')->default(0);
            $table->unsignedInteger('helpful_count')->default(0);
            $table->unsignedInteger('unhelpful_count')->default(0);
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'is_pinned', 'sort_order']);
            $table->index(['category_id', 'status']);
        });

        Schema::create('kb_feedback', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('article_id')->constrained('kb_articles')->cascadeOnDelete();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->boolean('is_helpful');
            $table->text('comment')->nullable();
            $table->string('ip_hash', 64)->index();
            $table->timestamp('created_at')->nullable();

            $table->index(['article_id', 'created_at']);
        });

        Schema::create('kb_search_misses', function (Blueprint $table): void {
            $table->id();
            $table->string('query')->unique();
            $table->unsignedInteger('hits_count')->default(1);
            $table->timestamp('last_searched_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kb_feedback');
        Schema::dropIfExists('kb_articles');
        Schema::dropIfExists('kb_categories');
        Schema::dropIfExists('kb_search_misses');
    }
};
