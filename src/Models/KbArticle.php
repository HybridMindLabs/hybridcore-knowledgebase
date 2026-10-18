<?php

namespace Hybridcore\Knowledgebase\Models;

use App\Models\Server;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class KbArticle extends Model
{
    protected $table = 'kb_articles';

    protected $fillable = [
        'category_id',
        'server_id',
        'author_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'status',
        'is_pinned',
        'is_featured',
        'sort_order',
        'views_count',
        'helpful_count',
        'unhelpful_count',
        'meta_title',
        'meta_description',
        'published_at',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
        'is_featured' => 'boolean',
        'sort_order' => 'integer',
        'views_count' => 'integer',
        'helpful_count' => 'integer',
        'unhelpful_count' => 'integer',
        'published_at' => 'datetime',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(KbCategory::class, 'category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class, 'server_id');
    }

    public function feedback(): HasMany
    {
        return $this->hasMany(KbFeedback::class, 'article_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopePinned(Builder $query): Builder
    {
        return $query->where('is_pinned', true);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function getHelpfulnessRatingAttribute(): int
    {
        $total = $this->helpful_count + $this->unhelpful_count;
        if ($total === 0) {
            return 100;
        }

        return (int) round(($this->helpful_count / $total) * 100);
    }

    public function getEstimatedReadingTimeAttribute(): int
    {
        $words = str_word_count(strip_tags($this->content ?? ''));
        return max(1, (int) ceil($words / 200));
    }

    protected static function booted(): void
    {
        static::creating(function (KbArticle $article): void {
            if (empty($article->slug)) {
                $article->slug = Str::slug($article->title);
            }
            if ($article->status === 'published' && empty($article->published_at)) {
                $article->published_at = now();
            }
        });
    }
}
