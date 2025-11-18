<?php
// app/Jobs/ProcessIntake.php
namespace App\Jobs;

use App\Models\IntakeLog;
use App\Models\Order;
use App\Models\OrderConfirmation;
use App\Models\OrderMatch;
use App\Models\OrderMismatch;
use App\Models\Exception as ExceptionModel; // ⚠️ alias
use App\Models\Invoice;
use App\Models\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProcessIntake implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function __construct(public int $intakeLogId) {}

    public function handle(): void
    {
        /** @var IntakeLog $log */
        $log = IntakeLog::findOrFail($this->intakeLogId);
        $log->update(['status' => 'processing']);

        try {
            DB::transaction(function () use ($log) {
                $body = $log->body;

                if (($body['type'] ?? null) === 'batch' && isset($body['records'])) {
                    foreach ($body['records'] as $rec) {
                        $this->ingestSingle($rec['type'], $rec['payload'], $log);
                    }
                } else {
                    $this->ingestSingle($body['type'], $body['payload'], $log);
                }
            });

            $log->update(['status' => 'done', 'error' => null]);
        } catch (\Throwable $e) {
            $log->update(['status' => 'failed', 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    protected function ingestSingle(string $type, array $payload, IntakeLog $log): void
    {
        // Optional: store PDF (url, base64, or uploaded files) and link to documents
        $documentId = null;
        
        // Handle direct file uploads (multipart/form-data)
        if (!empty($payload['uploaded_files']) && is_array($payload['uploaded_files'])) {
            // Use the first uploaded file (or you can handle multiple)
            $uploadedFile = $payload['uploaded_files'][0] ?? null;
            if ($uploadedFile && isset($uploadedFile['path'])) {
                $documentId = $this->attachDocument(
                    $uploadedFile['path'], 
                    $uploadedFile['custom_name'] ?? $uploadedFile['original_name'] ?? null
                );
            }
        }
        // Handle base64 encoded PDF
        elseif (!empty($payload['pdf_base64'])) {
            $path = 'incoming/'.date('Y/m/').Str::uuid().'.pdf';
            Storage::disk('public')->put($path, base64_decode($payload['pdf_base64']));
            $documentId = $this->attachDocument($path, $payload['file_name'] ?? null);
        }
        // Handle PDF URL
        elseif (!empty($payload['pdf_url'])) {
            // Either download or store external URL only (simpler):
            $documentId = $this->attachDocument($payload['pdf_url'], $payload['file_name'] ?? null, isExternal:true);
        }

        match ($type) {
            'order_confirmation' => $this->ingestConfirmation($payload, $documentId),
            'order_match'        => $this->ingestMatch($payload),
            'exception'          => $this->ingestException($payload),
            'invoice'            => $this->ingestInvoice($payload, $documentId),
            default              => null,
        };
    }

    protected function attachDocument(string $pathOrUrl, ?string $original = null, bool $isExternal=false): int
    {
        // Store original_name and is_external in description JSON since these columns don't exist
        $description = [];
        if ($original) {
            $description['original_name'] = $original;
        }
        if ($isExternal) {
            $description['is_external'] = true;
        }
        
        $doc = Document::create([
            'file_path' => $pathOrUrl,
            'title' => $original ?? basename($pathOrUrl),
            'description' => !empty($description) ? json_encode($description) : null,
        ]);
        return $doc->id;
    }

    protected function ingestConfirmation(array $p, ?int $documentId): void
    {
        // expected p: order_number, supplier, received_at, confidence, payload (raw)
        $orderId = Order::where('order_number', $p['order_number'])->value('id');

        $oc = OrderConfirmation::create([
            'order_id' => $orderId,
            'supplier' => $p['supplier'] ?? null,
            'received_at' => $p['received_at'] ?? now(),
            'confidence' => $p['confidence'] ?? null,
            'payload' => $p['raw'] ?? ($p['payload'] ?? null),
            'document_id' => $documentId,
        ]);
    }

    protected function ingestMatch(array $p): void
    {
        // Check if this is the new payload structure with metadata
        if (isset($p['metadata'])) {
            $documentType = $p['metadata']['document_type'] ?? 'order_confirmation';
            $overallStatus = $p['metadata']['overall_status'] ?? 'matched';
            
            // Handle invoice documents - but only if the document_type is actually invoice
            if ($documentType === 'invoice' || $documentType === 'inovice') {
                $this->ingestInvoiceMatch($p);
                return;
            }
            
            // Handle order confirmation documents (including mismatched ones)
            // This covers both matched and mismatched order confirmations
            $this->ingestOrderMatchOrMismatch($p, $overallStatus);
            return;
        }
        
        // Legacy logic for old payload structure
        // expected p: order_number, order_confirmation_id OR oc_order_number, strategy, score, result, matched_at, reviewed_by, notes
        $orderId = Order::where('order_number', $p['order_number'])->value('id');

        OrderMatch::create([
            'order_id' => $orderId,
            'order_confirmation_id' => $p['order_confirmation_id'] ?? null,
            'strategy' => $p['strategy'] ?? 'unknown',
            'score' => $p['score'] ?? null,
            'result' => $p['result'] ?? 'failure',
            'matched_at' => $p['matched_at'] ?? now(),
            'reviewed_by' => $p['reviewed_by'] ?? null,
            'notes' => $p['notes'] ?? null,
        ]);
    }
    
    protected function ingestOrderMatchOrMismatch(array $p, string $overallStatus): void
    {
        // Create order match/mismatch record for new payload structure
        $poNumber = $p['order_info']['po_number'] ?? $p['metadata']['pdf_filename'] ?? 'unknown';
        $customer = $p['order_info']['customer'] ?? 'Unknown Customer';
        
        // Find or create order
        $order = Order::firstOrCreate(
            ['po_number' => $poNumber],
            [
                'order_number' => $poNumber,
                'supplier' => $customer,
                'status' => 'open',
                'total_amount' => $p['summary']['total_amount'] ?? 0,
            ]
        );
        
        // Find or create order confirmation
        $orderConfirmation = OrderConfirmation::firstOrCreate(
            ['order_id' => $order->id],
            [
                'supplier' => $customer,
                'received_at' => now(),
                'confidence' => in_array($overallStatus, ['mismatched', 'needs_review']) ? 0.5 : 0.95,
                'status' => 'confirmed',
            ]
        );
        
        if (in_array($overallStatus, ['mismatched', 'needs_review'])) {
            // Create order mismatch record
            $mismatch = OrderMismatch::create([
                'order_id' => $order->id,
                'order_confirmation_id' => $orderConfirmation->id,
                'code' => 'mismatch',
                'severity' => 'medium',
                'status' => 'open',
                'message' => 'Order confirmation has mismatches between PDF and Excel data',
                'details' => [
                    'total_mismatches' => $p['metadata']['total_mismatches'] ?? 0,
                    'total_matches' => $p['metadata']['total_matches'] ?? 0,
                    'comparison_summary' => 'Mismatches detected in order confirmation',
                    'full_payload' => $p, // Store the full payload for comparison view
                ],
                'email' => $p['metadata']['sender_email'] ?? null,
                'email_sent' => $p['metadata']['email_report_sent'] ?? false,
                'email_sent_at' => ($p['metadata']['email_report_sent'] ?? false) ? now() : null,
                'created_by' => Auth::id(),
            ]);
            
            // Store the mismatch ID in the intake log for later retrieval
            $this->storeCreatedRecordId('order_mismatch_id', $mismatch->id);
        } else {
            // Create order match record (for matched, needs_review, or other successful statuses)
            $match = OrderMatch::create([
                'order_confirmation_id' => $orderConfirmation->id,
                'order_id' => $order->id,
                'po_number' => $poNumber,
                'customer' => $customer,
                'status' => 'processed',
                'result' => 'matched',
                'matched_at' => now(),
                'payload' => $p,
                'author_id' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);
            
            // Store the match ID in the intake log for later retrieval
            $this->storeCreatedRecordId('order_match_id', $match->id);
        }
    }
    
    protected function ingestInvoiceMatch(array $p): void
    {
        // Create invoice match record for new payload structure
        $poNumber = $p['order_info']['po_number'] ?? $p['metadata']['pdf_filename'] ?? 'unknown';
        $customer = $p['order_info']['customer'] ?? 'Unknown Customer';
        $overallStatus = $p['metadata']['overall_status'] ?? 'matched';
        
        // First, try to find an existing order with this PO number
        $order = Order::where('po_number', $poNumber)->first();
        
        // If no order exists, create one
        if (!$order) {
            $order = Order::create([
                'po_number' => $poNumber,
                'order_number' => $poNumber,
                'supplier' => $customer,
                'status' => 'open',
                'total_amount' => $p['summary']['total_amount'] ?? 0,
            ]);
        }
        
        
    }
    

    protected function ingestException(array $p): void
    {
        // expected p: order_number, order_confirmation_id, code, severity, status, message
        $orderId = Order::where('order_number', $p['order_number'])->value('id');

        ExceptionModel::create([
            'order_id' => $orderId,
            'order_confirmation_id' => $p['order_confirmation_id'] ?? null,
            'code' => $p['code'] ?? 'unknown',
            'severity' => $p['severity'] ?? 'medium',
            'status' => $p['status'] ?? 'open',
            'message' => $p['message'] ?? null,
            'created_by' => Auth::id(), // per your convention
        ]);
    }

    protected function ingestInvoice(array $p, ?int $documentId): void
    {
        // expected p: order_number, invoice_number, amount, currency, issued_at, due_at
        $orderId = Order::where('order_number', $p['order_number'])->value('id');

        Invoice::create([
            'order_id' => $orderId,
            'invoice_number' => $p['invoice_number'] ?? null,
            'amount' => $p['amount'] ?? null,
            'currency' => $p['currency'] ?? 'EUR',
            'issued_at' => $p['issued_at'] ?? now(),
            'due_at' => $p['due_at'] ?? null,
            'document_id' => $documentId,
        ]);
    }
    
    /**
     * Store the created record ID in the intake log for later retrieval
     */
    protected function storeCreatedRecordId(string $fieldName, int $recordId): void
    {
        $log = IntakeLog::find($this->intakeLogId);
        if ($log) {
            $metadata = $log->metadata ?? [];
            $metadata[$fieldName] = $recordId;
            $log->update(['metadata' => $metadata]);
        }
    }
}
