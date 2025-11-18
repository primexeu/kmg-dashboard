<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceMatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'invoice_number',
        'supplier',
        'status',
        'result',
        'matched_at',
        'confidence_score',
        'payload',
        'author_id',
        'updated_by',
    ];

    protected $casts = [
        'payload' => 'array',
        'matched_at' => 'datetime',
        'confidence_score' => 'decimal:4',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'warning',
            'processing' => 'info',
            'matched' => 'success',
            'mismatched' => 'danger',
            'completed' => 'success',
            default => 'gray',
        };
    }

    public function getResultColorAttribute(): string
    {
        return match ($this->result) {
            'matched' => 'success',
            'mismatched' => 'danger',
            'partial' => 'warning',
            'error' => 'danger',
            default => 'gray',
        };
    }

    public function isMatched(): bool
    {
        return $this->result === 'matched';
    }

    public function isMismatched(): bool
    {
        return $this->result === 'mismatched';
    }
}