<?php

namespace Hybridcore\Knowledgebase\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Hybridcore\Knowledgebase\Models\KbFeedback;
use Hybridcore\Knowledgebase\Models\KbSearchMiss;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FeedbackController extends Controller
{
    public function index(Request $request): Response
    {
        $feedbackQuery = KbFeedback::with(['article:id,title,slug,category_id', 'article.category:id,name,slug', 'user:id,name,username,avatar'])
            ->orderByDesc('created_at');

        if ($request->has('helpful')) {
            $feedbackQuery->where('is_helpful', filter_var($request->input('helpful'), FILTER_VALIDATE_BOOLEAN));
        }

        $feedback = $feedbackQuery->paginate(20, ['*'], 'feedback_page')->withQueryString();

        $searchMisses = KbSearchMiss::orderByDesc('hits_count')
            ->orderByDesc('last_searched_at')
            ->paginate(15, ['*'], 'misses_page');

        return Inertia::render('Extensions/hybridcore/knowledgebase/Admin/Feedback/Index', [
            'feedback' => $feedback,
            'searchMisses' => $searchMisses,
            'filters' => [
                'helpful' => $request->input('helpful', ''),
            ],
        ]);
    }

    public function destroy(KbFeedback $feedback): RedirectResponse
    {
        $feedback->delete();

        return back()->with('success', trans('knowledgebase::messages.feedback_deleted'));
    }

    public function clearMiss(KbSearchMiss $miss): RedirectResponse
    {
        $miss->delete();

        return back()->with('success', trans('knowledgebase::messages.search_miss_cleared'));
    }

    public function clearAllMisses(): RedirectResponse
    {
        KbSearchMiss::truncate();

        return back()->with('success', trans('knowledgebase::messages.all_search_misses_cleared'));
    }
}
