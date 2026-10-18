<?php

namespace Hybridcore\Knowledgebase\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Hybridcore\Knowledgebase\Models\KbArticle;
use Hybridcore\Knowledgebase\Services\KnowledgebaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function __construct(
        protected KnowledgebaseService $service
    ) {}

    public function store(Request $request, KbArticle $article): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'is_helpful' => ['required', 'boolean'],
            'comment' => ['nullable', 'string', 'max:500'],
        ]);

        $recorded = $this->service->recordFeedback(
            article: $article,
            isHelpful: (bool) $validated['is_helpful'],
            comment: $validated['comment'] ?? null,
            userId: $request->user()?->id,
            ip: (string) $request->ip(),
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => $recorded,
                'message' => $recorded
                    ? trans('knowledgebase::messages.feedback_recorded')
                    : trans('knowledgebase::messages.feedback_already_submitted'),
                'helpful_count' => $article->fresh()->helpful_count,
                'unhelpful_count' => $article->fresh()->unhelpful_count,
            ]);
        }

        if (! $recorded) {
            return back()->with('warning', trans('knowledgebase::messages.feedback_already_submitted'));
        }

        return back()->with('success', trans('knowledgebase::messages.feedback_recorded'));
    }
}
