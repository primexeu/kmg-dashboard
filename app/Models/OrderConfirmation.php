<?php

// app/Models/OrderConfirmation.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderConfirmation extends Model
{
    protected $table = 'order_confirmations';
    
    protected $fillable = [
        'order_id',
        'supplier',
        'received_at',
        'confidence',
        'payload',
        'status',
        'notes',
        'created_by',
        'updated_by',
    ];
    
    protected $casts = [
        'received_at' => 'datetime',
        'confidence' => 'decimal:2',
        'payload' => 'array',
    ];

    // Relationships
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function orderMismatches(): HasMany
    {
        return $this->hasMany(OrderMismatch::class);
    }

    public function orderMatches(): HasMany
    {
        return $this->hasMany(OrderMatch::class);
    }

    // Helper methods
    public function hasMismatches(): bool
    {
        return $this->orderMismatches()->where('status', '!=', 'resolved')->exists();
    }

    public function hasMatches(): bool
    {
        return $this->orderMatches()->exists();
    }

    public function getMismatchCount(): int
    {
        return $this->orderMismatches()->where('status', '!=', 'resolved')->count();
    }

    public function getMatchCount(): int
    {
        return $this->orderMatches()->count();
    }
}
