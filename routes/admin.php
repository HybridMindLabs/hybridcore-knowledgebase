<?php

use Hybridcore\Knowledgebase\Http\Controllers\Admin\ArticleController;
use Hybridcore\Knowledgebase\Http\Controllers\Admin\CategoryController;
use Hybridcore\Knowledgebase\Http\Controllers\Admin\FeedbackController;
use Hybridcore\Knowledgebase\Http\Controllers\Admin\SettingsController;
use Illuminate\Support\Facades\Route;

Route::middleware('perm:knowledgebase.manage')->group(function (): void {
    // Articles
    Route::resource('knowledgebase/articles', ArticleController::class)
        ->except(['show'])
        ->names([
            'index' => 'admin.knowledgebase.articles.index',
            'create' => 'admin.knowledgebase.articles.create',
            'store' => 'admin.knowledgebase.articles.store',
            'edit' => 'admin.knowledgebase.articles.edit',
            'update' => 'admin.knowledgebase.articles.update',
            'destroy' => 'admin.knowledgebase.articles.destroy',
        ]);

    // Categories
    Route::resource('knowledgebase/categories', CategoryController::class)
        ->except(['show', 'create', 'edit'])
        ->names([
            'index' => 'admin.knowledgebase.categories.index',
            'store' => 'admin.knowledgebase.categories.store',
            'update' => 'admin.knowledgebase.categories.update',
            'destroy' => 'admin.knowledgebase.categories.destroy',
        ]);
    Route::post('knowledgebase/categories/reorder', [CategoryController::class, 'reorder'])
        ->name('admin.knowledgebase.categories.reorder');

    // Feedback & Search misses
    Route::get('knowledgebase/feedback', [FeedbackController::class, 'index'])
        ->name('admin.knowledgebase.feedback.index');
    Route::delete('knowledgebase/feedback/{feedback}', [FeedbackController::class, 'destroy'])
        ->name('admin.knowledgebase.feedback.destroy');
    Route::delete('knowledgebase/search-misses/{miss}', [FeedbackController::class, 'clearMiss'])
        ->name('admin.knowledgebase.search-misses.clear');
    Route::post('knowledgebase/search-misses/clear-all', [FeedbackController::class, 'clearAllMisses'])
        ->name('admin.knowledgebase.search-misses.clear-all');

    // Extension settings page convention: /admin/settings/extensions/knowledgebase
    Route::get('settings/extensions/knowledgebase', [SettingsController::class, 'show'])
        ->name('admin.settings.extensions.knowledgebase');
    Route::patch('settings/extensions/knowledgebase', [SettingsController::class, 'update'])
        ->name('admin.settings.extensions.knowledgebase.update');
});
