<?php

// app/Models/OrderMatch.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderMatch extends Model
{
    protected $table = 'order_matches';

    protected $fillable = [
        'order_confirmation_id',
        'order_id',
        'po_number',
        'customer',
        'status',
        'payload',
        'author_id',
        'updated_by',
        'strategy',
        'score',
        'result',
        'matched_at',
        'reviewed_by',
        'notes',
    ];

    protected $casts = [
        'payload' => 'array',
        'score' => 'decimal:2',
        'matched_at' => 'datetime',
    ];

    // Relationships
    public function orderConfirmation(): BelongsTo
    {
        return $this->belongsTo(OrderConfirmation::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // Helper methods
    public function isMatched(): bool
    {
        return $this->result === 'matched';
    }

    public function needsReview(): bool
    {
        return $this->result === 'needs_review';
    }

    public function getResultColor(): string
    {
        return match ($this->result) {
            'matched' => 'success',
            'partial' => 'info',
            'needs_review' => 'warning',
            'unmatched' => 'danger',
            default => 'gray',
        };
    }

    public function getStatusColor(): string
    {
        return match ($this->status) {
            'processed' => 'success',
            'pending' => 'warning',
            'failed' => 'danger',
            default => 'gray',
        };
    }
}

