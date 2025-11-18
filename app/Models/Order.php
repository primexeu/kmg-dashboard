<?php

// app/Models/Order.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'order_number','supplier_code','supplier','customer_name','customer_email',
        'order_date','required_by','expected_delivery_date','closed_at','cancelled_at',
        'currency','subtotal_amount','tax_amount','total_amount','payment_terms','incoterm',
        'shipping_method','tracking_number','billing_address','delivery_address',
        'status','source','channel','po_number','tags','metadata','notes',
    ];

    protected $casts = [
        'order_date'             => 'date',
        'required_by'            => 'date',
        'expected_delivery_date' => 'date',
        'closed_at'              => 'datetime',
        'cancelled_at'           => 'datetime',
        'subtotal_amount'        => 'decimal:2',
        'tax_amount'             => 'decimal:2',
        'total_amount'           => 'decimal:2',
        'billing_address'        => 'array',
        'delivery_address'       => 'array',
        'tags'                   => 'array',
        'metadata'               => 'array',
    ];

    // Relationships
    public function orderConfirmations(): HasMany
    {
        return $this->hasMany(OrderConfirmation::class);
    }

    public function orderMismatches(): HasMany
    {
        return $this->hasMany(OrderMismatch::class);
    }

    public function orderMatches(): HasMany
    {
        return $this->hasMany(OrderMatch::class);
    }
}
