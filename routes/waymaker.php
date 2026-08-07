<?php

declare(strict_types=1);

use App\Http\Controllers\AgentDocsController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'show'])->name('HomeController.show');
Route::get('/create.md', [AgentDocsController::class, 'create'])->name('AgentDocsController.create');
Route::get('/llms.txt', [AgentDocsController::class, 'index'])->name('AgentDocsController.index');
