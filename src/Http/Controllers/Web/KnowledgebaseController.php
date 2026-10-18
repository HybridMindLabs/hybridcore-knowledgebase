<?php

namespace Hybridcore\Knowledgebase\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Hybridcore\Knowledgebase\Models\KbArticle;
use Hybridcore\Knowledgebase\Models\KbCategory;
use Hybridcore\Knowledgebase\Services\KnowledgebaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class KnowledgebaseController extends Controller
{
    public function __construct(
        protected KnowledgebaseService $service
    ) {}

    public function index(Request $request): Response
    {
        $categories = $this->service->getCategoriesWithCounts();
        $featured = $this->service->getFeaturedArticles(6);
        $popular = $this->service->getPopularArticles(5);

        return Inertia::render('Extensions/hybridcore/knowledgebase/Web/Index', [
            'categories' => $categories,
            'featuredArticles' => $featured,
            'popularArticles' => $popular,
            'totalArticles' => KbArticle::published()->count(),
        ]);
    }

    public function category(string $slug, Request $request): Response
    {
        $category = KbCategory::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $query = $category->articles()->published()->with(['server:id,name']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search): void {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('excerpt', 'like', "%{$search}%");
            });
        }

        $articles = $query->orderByDesc('is_pinned')
            ->orderBy('sort_order')
            ->orderByDesc('published_at')
            ->paginate((int) config('ext.knowledgebase.articles_per_page', 12))
            ->withQueryString();

        $allCategories = KbCategory::active()->get(['id', 'name', 'slug', 'icon', 'color']);

        return Inertia::render('Extensions/hybridcore/knowledgebase/Web/Category', [
            'category' => $category,
            'articles' => $articles,
            'allCategories' => $allCategories,
            'filters' => [
                'search' => $request->input('search', ''),
            ],
        ]);
    }

    public function article(string $categorySlug, string $articleSlug): Response
    {
        $category = KbCategory::where('slug', $categorySlug)
            ->where('is_active', true)
            ->firstOrFail();

        $article = KbArticle::where('category_id', $category->id)
            ->where('slug', $articleSlug)
            ->published()
            ->with(['author:id,name,username,avatar', 'server:id,name,ip,port'])
            ->firstOrFail();

        $this->service->incrementViews($article);

        // Related articles in the same category
        $related = KbArticle::where('category_id', $category->id)
            ->where('id', '!=', $article->id)
            ->published()
            ->orderBy('sort_order')
            ->orderByDesc('views_count')
            ->limit(4)
            ->get(['id', 'title', 'slug', 'excerpt', 'views_count']);

        // Adjacent articles (previous and next in category)
        $previous = KbArticle::where('category_id', $category->id)
            ->where('id', '<', $article->id)
            ->published()
            ->orderByDesc('id')
            ->first(['id', 'title', 'slug']);

        $next = KbArticle::where('category_id', $category->id)
            ->where('id', '>', $article->id)
            ->published()
            ->orderBy('id')
            ->first(['id', 'title', 'slug']);

        return Inertia::render('Extensions/hybridcore/knowledgebase/Web/Article', [
            'category' => $category,
            'article' => $article,
            'relatedArticles' => $related,
            'previousArticle' => $previous,
            'nextArticle' => $next,
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $term = (string) $request->input('q', '');
        $results = $this->service->search($term, 8);

        return response()->json([
            'results' => $results->map(fn (KbArticle $a) => [
                'id' => $a->id,
                'title' => $a->title,
                'slug' => $a->slug,
                'excerpt' => $a->excerpt,
                'category_name' => $a->category?->name,
                'category_slug' => $a->category?->slug,
                'url' => route('knowledgebase.article', [
                    'category' => $a->category?->slug,
                    'article' => $a->slug,
                ]),
            ]),
        ]);
    }
}
