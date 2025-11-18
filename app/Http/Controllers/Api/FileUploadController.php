<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\File;

class FileUploadController extends Controller
{
    /**
     * Simple file upload endpoint
     * Accepts files via multipart/form-data and returns document information
     * 
     * POST /api/upload-files
     */
    public function upload(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'files' => ['required', 'array', 'min:1'],
            'files.*' => [
                'required',
                File::types(['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png'])
                    ->max(16384), // 16MB
            ],
            'metadata' => ['nullable', 'array'],
            'metadata.comparison_id' => ['nullable', 'string'], // For linking files to results
            'metadata.order_number' => ['nullable', 'string'],
            'metadata.ab_number' => ['nullable', 'string'],
            'metadata.type' => ['nullable', 'string', 'in:order_confirmation,invoice,other'],
        ]);

        $uploadedDocuments = [];
        
        foreach ($request->file('files') as $file) {
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $path = 'incoming/' . date('Y/m/') . Str::uuid() . '.' . $extension;
            
            // Store the file
            Storage::disk('public')->putFileAs(
                dirname($path),
                $file,
                basename($path)
            );
            
            // Create Document record
            // Store original_name in description JSON since the column doesn't exist
            $metadata = $validated['metadata'] ?? [];
            $metadata['original_name'] = $originalName;
            
            $document = Document::create([
                'file_path' => $path,
                'title' => $validated['metadata']['type'] ?? 'Uploaded Document',
                'description' => json_encode($metadata),
            ]);
            
            $uploadedDocuments[] = [
                'document_id' => $document->id,
                'path' => $path,
                'url' => asset('storage/' . $path),
                'original_name' => $originalName,
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'uploaded_at' => now()->toIso8601String(),
            ];
        }

        return response()->json([
            'success' => true,
            'message' => count($uploadedDocuments) . ' file(s) uploaded successfully',
            'documents' => $uploadedDocuments,
            'count' => count($uploadedDocuments),
        ], 201);
    }
}
