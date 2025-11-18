<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

class PythonApiComparison extends Model
{
    protected $fillable = [
        'order_number',
        'ab_number',
        'customer_number',
        'customer_name',
        'commission',
        'order_header',
        'ab_header',
        'header_comparison',
        'items_comparison',
        'summary',
        'full_payload',
        'overall_status',
        'total_items',
        'matches',
        'mismatches',
        'review_required',
        'missing_in_order',
        'missing_in_confirmation',
        'processed_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'order_header' => 'array',
        'ab_header' => 'array',
        'header_comparison' => 'array',
        'items_comparison' => 'array',
        'summary' => 'array',
        'full_payload' => 'array',
        'processed_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get all uploaded files related to this comparison
     * Checks multiple sources: full_payload, Document table, and uploaded_files in payload
     */
    public function getUploadedFiles(): array
    {
        $files = [];

        // 1. Check full_payload for files in various possible locations
        // Python API might store files in different places depending on how it was called
        $payload = $this->full_payload ?? [];
        
        // Log for debugging (remove in production or use proper logging)
        Log::debug('PythonApiComparison::getUploadedFiles', [
            'comparison_id' => $this->id,
            'payload_keys' => array_keys($payload),
            'has_data' => isset($payload['data']),
            'data_keys' => isset($payload['data']) ? array_keys($payload['data']) : [],
        ]);
        
        // Try multiple possible locations where files might be stored
        $payloadFiles = 
            $payload['data']['files'] ?? 
            $payload['files'] ?? 
            $payload['data']['uploaded_files'] ?? 
            $payload['uploaded_files'] ?? 
            $payload['data']['file_info'] ?? 
            $payload['file_info'] ?? 
            $payload['data']['source_files'] ?? 
            $payload['source_files'] ?? 
            [];
            
        if (!empty($payloadFiles) && is_array($payloadFiles)) {
            foreach ($payloadFiles as $file) {
                // Handle both array format and object format
                if (is_array($file)) {
                    $files[] = [
                        'source' => 'api',
                        'filename' => $file['filename'] ?? $file['name'] ?? $file['original_name'] ?? 'Unknown',
                        'url' => $file['url'] ?? $file['path'] ?? null,
                        'type' => $file['type'] ?? $file['mime_type'] ?? null,
                        'size' => $file['size'] ?? null,
                        'field' => $file['field'] ?? $file['file_type'] ?? null,
                    ];
                }
            }
        }
        
        // 2. Check metadata for file references
        $metadata = $payload['metadata'] ?? $payload['data']['metadata'] ?? [];
        if (!empty($metadata)) {
            // Check for file paths in metadata
            if (isset($metadata['order_pdf_path']) || isset($metadata['ab_pdf_path'])) {
                if (isset($metadata['order_pdf_path'])) {
                    $path = $metadata['order_pdf_path'];
                    $files[] = [
                        'source' => 'api',
                        'filename' => 'Order PDF',
                        'url' => str_starts_with($path, 'http') ? $path : asset('storage/' . ltrim($path, '/')),
                        'type' => 'application/pdf',
                        'size' => null,
                        'field' => 'order_pdf',
                    ];
                }
                if (isset($metadata['ab_pdf_path'])) {
                    $path = $metadata['ab_pdf_path'];
                    $files[] = [
                        'source' => 'api',
                        'filename' => 'Order Confirmation PDF',
                        'url' => str_starts_with($path, 'http') ? $path : asset('storage/' . ltrim($path, '/')),
                        'type' => 'application/pdf',
                        'size' => null,
                        'field' => 'ab_pdf',
                    ];
                }
            }
        }
        
        // 3. Check if files were sent as base64 in the payload
        if (isset($payload['order_pdf']) || isset($payload['ab_pdf']) || isset($payload['data']['order_pdf']) || isset($payload['data']['ab_pdf'])) {
            $orderPdf = $payload['order_pdf'] ?? $payload['data']['order_pdf'] ?? null;
            $abPdf = $payload['ab_pdf'] ?? $payload['data']['ab_pdf'] ?? null;
            
            if ($orderPdf) {
                $files[] = [
                    'source' => 'api',
                    'filename' => 'Order PDF',
                    'url' => is_string($orderPdf) && (str_starts_with($orderPdf, 'http://') || str_starts_with($orderPdf, 'https://')) ? $orderPdf : null,
                    'type' => 'application/pdf',
                    'size' => null,
                    'field' => 'order_pdf',
                ];
            }
            
            if ($abPdf) {
                $files[] = [
                    'source' => 'api',
                    'filename' => 'Order Confirmation PDF',
                    'url' => is_string($abPdf) && (str_starts_with($abPdf, 'http://') || str_starts_with($abPdf, 'https://')) ? $abPdf : null,
                    'type' => 'application/pdf',
                    'size' => null,
                    'field' => 'ab_pdf',
                ];
            }
        }

        // 2. Check full_payload['uploaded_files'] (from intake API with file uploads)
        $uploadedFiles = $this->full_payload['uploaded_files'] ?? [];
        if (!empty($uploadedFiles) && is_array($uploadedFiles)) {
            foreach ($uploadedFiles as $file) {
                $path = $file['path'] ?? '';
                $files[] = [
                    'source' => 'upload',
                    'filename' => $file['custom_name'] ?? $file['original_name'] ?? basename($path),
                    'url' => $path ? asset('storage/' . $path) : null,
                    'path' => $path,
                    'type' => $file['mime_type'] ?? null,
                    'size' => $file['size'] ?? null,
                ];
            }
        }

        // 3. Check Document table by comparison_id, order_number, or ab_number
        // First, check if there's a comparison_id in metadata
        $comparisonId = $payload['metadata']['comparison_id'] ?? $payload['data']['metadata']['comparison_id'] ?? null;
        
        if ($comparisonId || $this->order_number || $this->ab_number) {
            // Skip if both are "Unknown" to avoid unnecessary queries
            if ($this->order_number === 'Unknown' && $this->ab_number === 'Unknown' && !$comparisonId) {
                return $files;
            }
            
            $documents = \App\Models\Document::where(function($query) use ($comparisonId) {
                // First, try to find by comparison_id if available
                if ($comparisonId) {
                    $query->where('description', 'like', '%"comparison_id":"' . $comparisonId . '"%')
                          ->orWhere('description', 'like', '%comparison_id%' . $comparisonId . '%');
                }
                // Only search if order_number is not "Unknown"
                if ($this->order_number && $this->order_number !== 'Unknown') {
                    $query->where('description', 'like', '%"order_number":"' . $this->order_number . '"%')
                          ->orWhere('description', 'like', '%order_number%' . $this->order_number . '%');
                }
                
                // Only search if ab_number is not "Unknown"
                if ($this->ab_number && $this->ab_number !== 'Unknown') {
                    $query->orWhere('description', 'like', '%"ab_number":"' . $this->ab_number . '"%')
                          ->orWhere('description', 'like', '%ab_number%' . $this->ab_number . '%');
                }
            })->orWhere(function($query) use ($comparisonId) {
                // Also check if file_path contains comparison_id, order or AB number
                if ($comparisonId) {
                    $query->where('file_path', 'like', '%' . $comparisonId . '%');
                }
                if ($this->order_number && $this->order_number !== 'Unknown') {
                    $query->orWhere('file_path', 'like', '%' . $this->order_number . '%');
                }
                if ($this->ab_number && $this->ab_number !== 'Unknown') {
                    $query->orWhere('file_path', 'like', '%' . $this->ab_number . '%');
                }
            })->get();

            foreach ($documents as $doc) {
                // Document model uses 'file_path' (from migration), but ProcessIntake might use 'path'
                $path = $doc->file_path ?? '';
                
                // Parse description JSON to get original_name and is_external
                $description = [];
                if ($doc->description) {
                    $description = json_decode($doc->description, true) ?? [];
                }
                $originalName = $description['original_name'] ?? basename($path);
                $isExternal = $description['is_external'] ?? (str_starts_with($path, 'http://') || str_starts_with($path, 'https://'));
                
                if ($path) {
                    if (!$isExternal) {
                        // Local file
                        $files[] = [
                            'source' => 'document',
                            'filename' => $originalName,
                            'url' => asset('storage/' . $path),
                            'path' => $path,
                            'type' => null,
                            'size' => null,
                            'document_id' => $doc->id,
                        ];
                    } else {
                        // External URL
                        $files[] = [
                            'source' => 'document_url',
                            'filename' => $originalName,
                            'url' => $path,
                            'path' => $path,
                            'type' => null,
                            'size' => null,
                            'document_id' => $doc->id,
                        ];
                    }
                }
            }
        }

        return $files;
    }

    /**
     * Get the overall verdict from header comparison
     */
    public function getOverallVerdictAttribute(): string
    {
        return $this->header_comparison['overall']['verdict'] ?? 'Unknown';
    }

    /**
     * Get the overall reason from header comparison
     */
    public function getOverallReasonAttribute(): string
    {
        return $this->header_comparison['overall']['reason'] ?? '';
    }

    /**
     * Check if this comparison has mismatches
     */
    public function hasMismatches(): bool
    {
        return $this->mismatches > 0 || $this->review_required > 0 || $this->missing_in_order > 0 || $this->missing_in_confirmation > 0;
    }

    /**
     * Get items that need review
     */
    public function getItemsNeedingReview(): array
    {
        if (!$this->items_comparison) {
            return [];
        }

        return array_filter($this->items_comparison, function ($item) {
            return in_array($item['Urteil'] ?? '', ['Prüfung erforderlich', 'Keine Übereinstimmung']);
        });
    }

    /**
     * Get items that match perfectly
     */
    public function getMatchingItems(): array
    {
        if (!$this->items_comparison) {
            return [];
        }

        return array_filter($this->items_comparison, function ($item) {
            return ($item['Urteil'] ?? '') === 'Übereinstimmung';
        });
    }

    /**
     * Get items missing in order
     */
    public function getItemsMissingInOrder(): array
    {
        if (!$this->items_comparison) {
            return [];
        }

        return array_filter($this->items_comparison, function ($item) {
            return ($item['Urteil'] ?? '') === 'Fehlt in Bestellung';
        });
    }

    /**
     * Get items missing in confirmation
     */
    public function getItemsMissingInConfirmation(): array
    {
        if (!$this->items_comparison) {
            return [];
        }

        return array_filter($this->items_comparison, function ($item) {
            return ($item['Urteil'] ?? '') === 'Fehlt in Auftragsbestätigung';
        });
    }
}
