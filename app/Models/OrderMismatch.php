<?php

// app/Models/OrderMismatch.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class OrderMismatch extends Model
{
    protected $table = 'order_mismatches';
    
    protected $fillable = [
        'order_confirmation_id',
        'order_id',
        'order_match_id',
        'code',
        'severity',
        'status',
        'message',
        'details',
        'email',
        'email_sent',
        'email_sent_at',
        'created_by',
        'resolved_by',
        'resolved_at',
    ];

    protected $casts = [
        'details' => 'array',
        'resolved_at' => 'datetime',
        'email_sent' => 'boolean',
        'email_sent_at' => 'datetime',
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

    public function orderMatch(): BelongsTo
    {
        return $this->belongsTo(OrderMatch::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    // Helper methods
    public function isResolved(): bool
    {
        return $this->status === 'resolved';
    }

    public function resolve(?User $user = null): void
    {
        $this->update([
            'status' => 'resolved',
            'resolved_by' => $user?->getKey() ?? Auth::id() ?? 1,
            'resolved_at' => now(),
        ]);
    }

    public function getSeverityColor(): string
    {
        return match ($this->severity) {
            'critical' => 'danger',
            'high' => 'warning',
            'medium' => 'info',
            'low' => 'gray',
            default => 'gray',
        };
    }

    public function getStatusColor(): string
    {
        return match ($this->status) {
            'resolved' => 'success',
            'in_progress' => 'warning',
            'open' => 'gray',
            default => 'gray',
        };
    }
}
