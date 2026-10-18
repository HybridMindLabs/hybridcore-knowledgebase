<?php

namespace Hybridcore\Knowledgebase\Services;

use Hybridcore\Knowledgebase\Models\KbArticle;
use Hybridcore\Knowledgebase\Models\KbCategory;
use Hybridcore\Knowledgebase\Models\KbFeedback;
use Hybridcore\Knowledgebase\Models\KbSearchMiss;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class KnowledgebaseService
{
    /** Search published articles with ranking */
    public function search(string $term, int $limit = 10): Collection
    {
        $cleanTerm = trim($term);
        if ($cleanTerm === '') {
            return new Collection();
        }

        $results = KbArticle::query()
            ->with(['category:id,name,slug,icon,color'])
            ->published()
            ->where(function ($q) use ($cleanTerm): void {
                $q->where('title', 'like', '%'.$cleanTerm.'%')
                  ->orWhere('excerpt', 'like', '%'.$cleanTerm.'%')
                  ->orWhere('content', 'like', '%'.$cleanTerm.'%');
            })
            ->orderByDesc('is_pinned')
            ->orderByDesc('views_count')
            ->limit($limit)
            ->get();

        if ($results->isEmpty() && strlen($cleanTerm) >= 3) {
            $this->recordSearchMiss($cleanTerm);
        }

        return $results;
    }

    /** Record user feedback */
    public function recordFeedback(KbArticle $article, bool $isHelpful, ?string $comment, ?int $userId, string $ip): bool
    {
        $ipHash = hash('sha256', $ip);

        // Check if this IP or user voted on this article in the last 24h
        $alreadyVoted = KbFeedback::where('article_id', $article->id)
            ->where(function ($q) use ($userId, $ipHash): void {
                if ($userId) {
                    $q->where('user_id', $userId)->orWhere('ip_hash', $ipHash);
                } else {
                    $q->where('ip_hash', $ipHash);
                }
            })
            ->where('created_at', '>=', now()->subDay())
            ->exists();

        if ($alreadyVoted) {
            return false;
        }

        KbFeedback::create([
            'article_id' => $article->id,
            'user_id' => $userId,
            'is_helpful' => $isHelpful,
            'comment' => $comment ? trim(strip_tags($comment)) : null,
            'ip_hash' => $ipHash,
            'created_at' => now(),
        ]);

        if ($isHelpful) {
            $article->increment('helpful_count');
        } else {
            $article->increment('unhelpful_count');
        }

        return true;
    }

    /** Record queries with zero hits for analytics */
    public function recordSearchMiss(string $term): void
    {
        try {
            if (! Schema::hasTable('kb_search_misses')) {
                return;
            }

            $normalized = Str::lower(substr(trim($term), 0, 100));
            $miss = KbSearchMiss::firstOrNew(['query' => $normalized]);
            $miss->hits_count = ($miss->hits_count ?? 0) + 1;
            $miss->last_searched_at = now();
            $miss->save();
        } catch (\Throwable) {
            // Ignore analytics recording errors
        }
    }

    /** Increment article view counter */
    public function incrementViews(KbArticle $article): void
    {
        try {
            $article->increment('views_count');
        } catch (\Throwable) {
            // Ignore
        }
    }

    /** Active categories with count of published articles */
    public function getCategoriesWithCounts(): Collection
    {
        return KbCategory::query()
            ->active()
            ->withCount(['articles' => fn ($q) => $q->published()])
            ->get();
    }

    /** Featured articles */
    public function getFeaturedArticles(int $limit = 6): Collection
    {
        return KbArticle::query()
            ->with(['category:id,name,slug,icon,color'])
            ->published()
            ->featured()
            ->orderBy('sort_order')
            ->orderByDesc('published_at')
            ->limit($limit)
            ->get();
    }

    /** Popular articles */
    public function getPopularArticles(int $limit = 5): Collection
    {
        return KbArticle::query()
            ->with(['category:id,name,slug,icon,color'])
            ->published()
            ->orderByDesc('views_count')
            ->limit($limit)
            ->get();
    }

    /** Overall metrics for dashboard widgets */
    public function getStats(): array
    {
        try {
            if (! Schema::hasTable('kb_articles')) {
                return ['articles_count' => 0, 'views_count' => 0, 'helpful_ratio' => 100];
            }

            $articlesCount = KbArticle::where('status', 'published')->count();
            $viewsCount = (int) KbArticle::sum('views_count');
            $helpful = (int) KbArticle::sum('helpful_count');
            $unhelpful = (int) KbArticle::sum('unhelpful_count');
            $totalVotes = $helpful + $unhelpful;
            $helpfulRatio = $totalVotes > 0 ? (int) round(($helpful / $totalVotes) * 100) : 100;

            return [
                'articles_count' => $articlesCount,
                'views_count' => $viewsCount,
                'helpful_ratio' => $helpfulRatio,
            ];
        } catch (\Throwable) {
            return ['articles_count' => 0, 'views_count' => 0, 'helpful_ratio' => 100];
        }
    }
}
