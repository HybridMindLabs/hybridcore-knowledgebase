<?php

namespace Hybridcore\Knowledgebase;

use App\Services\Extensions\Registries\ExtensionRegistry;
use App\Services\SettingsService;
use App\Support\Slots;
use Hybridcore\Knowledgebase\Models\KbArticle;
use Hybridcore\Knowledgebase\Services\KnowledgebaseService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class KnowledgebaseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(KnowledgebaseService::class);
    }

    public function boot(ExtensionRegistry $registry): void
    {
        // ── Permissions ──────────────────────────────────────────
        $registry->permissions()->register('knowledgebase.view', 'View Knowledgebase', 'knowledgebase');
        $registry->permissions()->register('knowledgebase.manage', 'Manage Knowledgebase', 'knowledgebase');

        // ── Admin Navigation ─────────────────────────────────────
        $registry->navigation()->register(
            label: 'knowledgebase::messages.nav',
            route: 'admin.knowledgebase.articles.index',
            icon: 'BookOpen',
            section: 'navigation.sections.content',
            permission: 'knowledgebase.manage',
            sort: 55,
        );

        // ── Public Header Navigation ─────────────────────────────
        $registry->publicNavigation()->register(
            label: 'knowledgebase::messages.nav',
            route: 'knowledgebase.index',
            icon: 'BookOpen',
            sort: 55,
            permission: null,
        );

        // ── User Dropdown Menu ───────────────────────────────────
        $registry->userMenu()->register(
            label: 'knowledgebase::messages.nav',
            route: 'knowledgebase.index',
            icon: 'BookOpen',
            sort: 55,
        );

        // ── Footer Link ──────────────────────────────────────────
        $registry->footerLinks()->register(
            label: 'knowledgebase::messages.nav',
            route: 'knowledgebase.index',
            sort: 55,
        );

        // ── Admin Command Palette (Quick Actions) ────────────────
        $registry->quickActions()->register(
            label: 'knowledgebase::messages.nav',
            route: 'admin.knowledgebase.articles.index',
            icon: 'BookOpen',
            permission: 'knowledgebase.manage',
            keywords: ['kb', 'knowledgebase', 'docs', 'guides', 'articles', 'rules', 'faq'],
        );

        // ── Global Search Provider ───────────────────────────────
        $registry->search()->register(
            key: 'knowledgebase_articles',
            label: 'knowledgebase::messages.nav',
            icon: 'BookOpen',
            resolver: function (string $term, int $limit) {
                if (! Schema::hasTable('kb_articles')) {
                    return [];
                }

                return KbArticle::query()
                    ->published()
                    ->with('category:id,name,slug')
                    ->where(function ($q) use ($term): void {
                        $q->where('title', 'like', "%{$term}%")
                          ->orWhere('excerpt', 'like', "%{$term}%");
                    })
                    ->limit($limit)
                    ->get()
                    ->map(fn (KbArticle $a) => [
                        'title' => $a->title,
                        'url' => route('knowledgebase.article', [
                            'category' => $a->category?->slug ?? 'general',
                            'article' => $a->slug,
                        ]),
                        'meta' => $a->category?->name ?? '',
                    ])
                    ->all();
            },
            permission: null,
        );

        // ── Dashboard Stat Card Widget ───────────────────────────
        $registry->widgets()->register(
            id: 'knowledgebase.stats',
            title: 'knowledgebase::messages.widget_title',
            component: 'stat',
            data: fn () => [
                'value' => $this->safePublishedCount(),
                'icon' => 'BookOpen',
                'label' => trans('knowledgebase::messages.widget_label'),
                'accent' => 'blue',
            ],
            permission: 'knowledgebase.manage',
            sort: 55,
        );

        // ── Settings Page ────────────────────────────────────────
        $registry->settings()->register(
            slug: 'knowledgebase',
            label: 'knowledgebase::messages.nav',
            route: 'admin.settings.extensions.knowledgebase',
            permission: 'knowledgebase.manage',
        );

        // ── Home Sidebar Slot Widget ─────────────────────────────
        $registry->slots()->register(
            slot: Slots::HOME_RIGHT_BOTTOM,
            component: 'HybridcoreKnowledgebaseWidget',
            data: function () {
                $settings = $this->app->make(SettingsService::class);
                if (! $settings->get('knowledgebase.show_home_widget', true)) {
                    return null;
                }

                if (! Schema::hasTable('kb_articles')) {
                    return null;
                }

                $articles = KbArticle::query()
                    ->published()
                    ->with('category:id,name,slug,color')
                    ->orderByDesc('is_pinned')
                    ->orderByDesc('views_count')
                    ->limit((int) config('ext.knowledgebase.home_widget_limit', 5))
                    ->get(['id', 'title', 'slug', 'category_id', 'views_count']);

                if ($articles->isEmpty()) {
                    return null;
                }

                return [
                    'articles' => $articles->map(fn ($a) => [
                        'id' => $a->id,
                        'title' => $a->title,
                        'slug' => $a->slug,
                        'category_name' => $a->category?->name,
                        'category_slug' => $a->category?->slug,
                        'category_color' => $a->category?->color,
                        'views_count' => $a->views_count,
                    ])->values()->all(),
                ];
            },
            priority: 45,
        );
    }

    private function safePublishedCount(): int
    {
        try {
            if (! Schema::hasTable('kb_articles')) {
                return 0;
            }

            return KbArticle::where('status', 'published')->count();
        } catch (\Throwable) {
            return 0;
        }
    }
}
