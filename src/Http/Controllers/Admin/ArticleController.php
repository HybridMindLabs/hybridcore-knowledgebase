<?php

namespace Hybridcore\Knowledgebase\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Server;
use Hybridcore\Knowledgebase\Http\Requests\ArticleRequest;
use Hybridcore\Knowledgebase\Models\KbArticle;
use Hybridcore\Knowledgebase\Models\KbCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ArticleController extends Controller
{
    public function index(Request $request): Response
    {
        $query = KbArticle::with(['category:id,name,color', 'server:id,name']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search): void {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $articles = $query->orderByDesc('is_pinned')
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $categories = KbCategory::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Extensions/hybridcore/knowledgebase/Admin/Articles/Index', [
            'articles' => $articles,
            'categories' => $categories,
            'filters' => [
                'search' => $request->input('search', ''),
                'category_id' => $request->input('category_id', ''),
                'status' => $request->input('status', ''),
            ],
        ]);
    }

    public function create(): Response
    {
        $categories = KbCategory::orderBy('sort_order')->orderBy('name')->get(['id', 'name']);
        $servers = $this->getServersList();

        return Inertia::render('Extensions/hybridcore/knowledgebase/Admin/Articles/Form', [
            'categories' => $categories,
            'servers' => $servers,
            'article' => null,
        ]);
    }

    public function store(ArticleRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        if ($data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        $data['author_id'] = $request->user()?->id;

        KbArticle::create($data);

        return redirect()->route('admin.knowledgebase.articles.index')
            ->with('success', trans('knowledgebase::messages.article_created'));
    }

    public function edit(KbArticle $article): Response
    {
        $categories = KbCategory::orderBy('sort_order')->orderBy('name')->get(['id', 'name']);
        $servers = $this->getServersList();

        return Inertia::render('Extensions/hybridcore/knowledgebase/Admin/Articles/Form', [
            'categories' => $categories,
            'servers' => $servers,
            'article' => $article,
        ]);
    }

    public function update(ArticleRequest $request, KbArticle $article): RedirectResponse
    {
        $data = $request->validated();

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        if ($data['status'] === 'published' && empty($article->published_at)) {
            $data['published_at'] = now();
        }

        $article->update($data);

        return redirect()->route('admin.knowledgebase.articles.index')
            ->with('success', trans('knowledgebase::messages.article_updated'));
    }

    public function destroy(KbArticle $article): RedirectResponse
    {
        $article->delete();

        return redirect()->route('admin.knowledgebase.articles.index')
            ->with('success', trans('knowledgebase::messages.article_deleted'));
    }

    private function getServersList(): array
    {
        try {
            if (Schema::hasTable('servers')) {
                return Server::orderBy('name')->get(['id', 'name'])->toArray();
            }
        } catch (\Throwable) {
            // Ignore
        }

        return [];
    }
}
