<?php

namespace Hybridcore\Knowledgebase\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    public function show(SettingsService $settings): Response
    {
        return Inertia::render('Extensions/hybridcore/knowledgebase/Admin/Settings', [
            'settings' => [
                'articles_per_page' => (int) $settings->get('knowledgebase.articles_per_page', 12),
                'enable_feedback' => (bool) $settings->get('knowledgebase.enable_feedback', true),
                'enable_search_tracking' => (bool) $settings->get('knowledgebase.enable_search_tracking', true),
                'show_toc' => (bool) $settings->get('knowledgebase.show_toc', true),
                'show_views_count' => (bool) $settings->get('knowledgebase.show_views_count', true),
                'show_home_widget' => (bool) $settings->get('knowledgebase.show_home_widget', true),
            ],
        ]);
    }

    public function update(Request $request, SettingsService $settings): RedirectResponse
    {
        $validated = $request->validate([
            'articles_per_page' => ['required', 'integer', 'min:3', 'max:50'],
            'enable_feedback' => ['required', 'boolean'],
            'enable_search_tracking' => ['required', 'boolean'],
            'show_toc' => ['required', 'boolean'],
            'show_views_count' => ['required', 'boolean'],
            'show_home_widget' => ['required', 'boolean'],
        ]);

        foreach ($validated as $key => $value) {
            $settings->set("knowledgebase.{$key}", $value);
        }

        return back()->with('success', trans('knowledgebase::messages.settings_saved'));
    }
}
