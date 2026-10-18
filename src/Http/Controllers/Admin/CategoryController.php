<?php

namespace Hybridcore\Knowledgebase\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Hybridcore\Knowledgebase\Http\Requests\CategoryRequest;
use Hybridcore\Knowledgebase\Models\KbCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function index(): Response
    {
        $categories = KbCategory::withCount('articles')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return Inertia::render('Extensions/hybridcore/knowledgebase/Admin/Categories/Index', [
            'categories' => $categories,
        ]);
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }
        if (! isset($data['sort_order'])) {
            $data['sort_order'] = (KbCategory::max('sort_order') ?? 0) + 1;
        }

        KbCategory::create($data);

        return back()->with('success', trans('knowledgebase::messages.category_created'));
    }

    public function update(CategoryRequest $request, KbCategory $category): RedirectResponse
    {
        $data = $request->validated();
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $category->update($data);

        return back()->with('success', trans('knowledgebase::messages.category_updated'));
    }

    public function destroy(KbCategory $category): RedirectResponse
    {
        $category->delete();

        return back()->with('success', trans('knowledgebase::messages.category_deleted'));
    }

    public function reorder(Request $request): RedirectResponse
    {
        $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['integer', 'exists:kb_categories,id'],
        ]);

        foreach ($request->input('order') as $index => $id) {
            KbCategory::where('id', $id)->update(['sort_order' => $index + 1]);
        }

        return back()->with('success', trans('knowledgebase::messages.order_updated'));
    }
}
