<?php

// app/Http/Controllers/Api/IntakeController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\IntakeRequest;
use App\Jobs\ProcessIntake;
use App\Models\IntakeLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class IntakeController extends Controller
{
    public function store(IntakeRequest $request): JsonResponse
    {
        $key = $request->idempotencyKey();

        // Handle direct file uploads if present
        $body = $request->all();
        if ($request->hasFile('files')) {
            $uploadedFiles = [];
            $fileNames = $request->input('payload.file_names', []);
            
            foreach ($request->file('files') as $index => $file) {
                $originalName = $file->getClientOriginalName();
                $customName = $fileNames[$index] ?? $originalName;
                $path = 'incoming/' . date('Y/m/') . Str::uuid() . '.' . $file->getClientOriginalExtension();
                
                // Store the file
                Storage::disk('public')->putFileAs(
                    dirname($path),
                    $file,
                    basename($path)
                );
                
                $uploadedFiles[] = [
                    'path' => $path,
                    'original_name' => $originalName,
                    'custom_name' => $customName,
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                ];
            }
            
            // Add file paths to payload for processing
            if (!isset($body['payload'])) {
                $body['payload'] = [];
            }
            $body['payload']['uploaded_files'] = $uploadedFiles;
        }

        // Upsert a log so we can dedupe quickly
        $log = IntakeLog::firstOrCreate(
            ['idempotency_key' => $key],
            [
                'source' => $request->string('source'),
                'type' => $request->string('type'),
                'body' => $body,
                'status' => 'queued',
            ],
        );

        // Already processed? Check if we have redirect URLs
        if ($log->wasRecentlyCreated === false && in_array($log->status, ['done','processing'])) {
            $response = ['status' => 'duplicate'];
            
            // If processing is done, check for created record IDs and provide redirect URLs
            if ($log->status === 'done' && $log->metadata) {
                $redirectUrl = $this->getRedirectUrlFromMetadata($log->metadata);
                if ($redirectUrl) {
                    $response['redirect_url'] = $redirectUrl;
                }
            }
            
            return response()->json($response, 200);
        }

        dispatch(new ProcessIntake($log->id))->onQueue('intake');

        return response()->json(['status' => 'accepted'], 202);
    }
    
    /**
     * Get redirect URL from metadata based on created record type
     */
    private function getRedirectUrlFromMetadata(array $metadata): ?string
    {
        // Check for order mismatch
        if (isset($metadata['order_mismatch_id'])) {
            return route('admin.order-mismatches.compare', ['mismatchId' => $metadata['order_mismatch_id']]);
        }
        
        return null;
    }
}
