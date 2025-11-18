<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\IntakeController;
use App\Http\Controllers\Api\FileUploadController;
use App\Http\Controllers\ComparisonController;
use App\Http\Controllers\PushComparisonResultsController;

Route::post('/intake', [IntakeController::class, 'store'])->middleware('intake.sig');

// Simple file upload endpoint (no signature required, but should be protected in production)
Route::post('/upload-files', [FileUploadController::class, 'upload']);

// Test endpoint for JSON comparison (for external API calls)
Route::post('/test-json', [ComparisonController::class, 'processJsonComparison']);
Route::post('/compare-json', [ComparisonController::class, 'processJsonComparison']);

// Fetch results from Python API
Route::get('/fetch-results', [ComparisonController::class, 'fetchApiResults']);

// Check for new results from Python API (for automatic processing)
Route::get('/check-results', [ComparisonController::class, 'checkForNewResults']);

// Push results from Python API (for automatic processing)
Route::post('/push-results', [PushComparisonResultsController::class, '__invoke']);

// Alias for Python API's /api/update endpoint (used by Streamlit/Python API)
Route::post('/update', [PushComparisonResultsController::class, '__invoke']);