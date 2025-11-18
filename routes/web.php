<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ComparisonController;
use App\Http\Controllers\PushComparisonResultsController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\PythonApiComparisonController;

Route::post('/ingest/comparison-results', PushComparisonResultsController::class)
    ->name('ingest.comparison-results')
    ->middleware(['web','auth']);

Route::get('/', function () {return view('welcome');})->name('welcome');

Route::get('/docs', function () {return view('documentation');})->name('docs');

// Test API integration page
Route::get('/test-api', function () {return view('test-api');})->name('test.api');

// Language switching route
Route::get('/language/{locale}', [LanguageController::class, 'switch'])->name('language.switch');




// Fetch results from Python API
Route::get('/fetch-api-results', [ComparisonController::class, 'fetchApiResults'])->name('fetch.api.results');

// Check for new results from Python API
Route::get('/check-api-results', [ComparisonController::class, 'checkForNewResults'])->name('check.api.results');

// Debug API connection
Route::get('/debug-api', [ComparisonController::class, 'debugApiConnection'])->name('debug.api');

// Python API Comparison PDF Download
Route::get('/admin/python-api-comparisons/{id}/download-summary', [PythonApiComparisonController::class, 'downloadSummary'])
    ->name('python-api-comparison.download-summary')
    ->middleware(['web', 'auth']);




