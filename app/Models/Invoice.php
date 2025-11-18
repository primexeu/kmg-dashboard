<?php

// app/Models/Invoice.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_number','order_id','supplier','issued_at','due_at',
        'subtotal_amount','tax_amount','total_amount','currency',
        'status','pdf_path','created_by','updated_by',
    ];

    protected $casts = [
        'issued_at'      => 'date',
        'due_at'         => 'date',
        'subtotal_amount'=> 'decimal:2',
        'tax_amount'     => 'decimal:2',
        'total_amount'   => 'decimal:2',
    ];

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

    public function invoiceMismatches(): HasMany
    {
        return $this->hasMany(InvoiceMismatch::class);
    }

    public function invoiceMatches(): HasMany
    {
        return $this->hasMany(InvoiceMatch::class);
    }

    public function hasMismatches(): bool
    {
        return $this->invoiceMismatches()->exists();
    }

    public function hasMatches(): bool
    {
        return $this->invoiceMatches()->exists();
    }

    public function getMismatchCountAttribute(): int
    {
        return $this->invoiceMismatches()->count();
    }

    public function getMatchCountAttribute(): int
    {
        return $this->invoiceMatches()->count();
    }

    public function getInvoiceTypeAttribute(): string
    {
        if ($this->hasMismatches()) {
            return 'mismatched';
        } elseif ($this->hasMatches()) {
            return 'matched';
        }
        return 'pending';
    }
}
