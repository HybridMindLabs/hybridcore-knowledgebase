<?php

use Hybridcore\Knowledgebase\Http\Controllers\Web\KnowledgebaseController;
use Illuminate\Support\Facades\Route;

Route::get('/knowledgebase/search', [KnowledgebaseController::class, 'search'])
    ->name('api.knowledgebase.search');
