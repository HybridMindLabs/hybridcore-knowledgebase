<?php

use Hybridcore\Knowledgebase\Http\Controllers\Web\FeedbackController;
use Hybridcore\Knowledgebase\Http\Controllers\Web\KnowledgebaseController;
use Illuminate\Support\Facades\Route;

Route::get('/kb', [KnowledgebaseController::class, 'index'])->name('knowledgebase.index');
Route::get('/kb/search', [KnowledgebaseController::class, 'search'])->name('knowledgebase.search');
Route::get('/kb/{category:slug}', [KnowledgebaseController::class, 'category'])->name('knowledgebase.category');
Route::get('/kb/{category:slug}/{article:slug}', [KnowledgebaseController::class, 'article'])->name('knowledgebase.article');

Route::post('/kb/articles/{article}/feedback', [FeedbackController::class, 'store'])
    ->middleware('throttle:30,1')
    ->name('knowledgebase.feedback');
