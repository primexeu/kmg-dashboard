<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceMismatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'invoice_match_id', // Link to the comparison data
        'mismatch_type',
        'description',
        'severity',
        'status',
        'details',
        'reported_by',
        'assigned_to',
        'resolved_at',
        'resolution_notes',
    ];

    protected $casts = [
        'details' => 'array',
        'resolved_at' => 'datetime',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function invoiceMatch(): BelongsTo
    {
        return $this->belongsTo(InvoiceMatch::class);
    }

    public function reportedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function getSeverityColorAttribute(): string
    {
        return match ($this->severity) {
            'low' => 'success',
            'medium' => 'warning',
            'high' => 'danger',
            'critical' => 'danger',
            default => 'gray',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'open' => 'warning',
            'investigating' => 'info',
            'resolved' => 'success',
            'closed' => 'gray',
            default => 'gray',
        };
    }

    public function isResolved(): bool
    {
        return in_array($this->status, ['resolved', 'closed']);
    }
}