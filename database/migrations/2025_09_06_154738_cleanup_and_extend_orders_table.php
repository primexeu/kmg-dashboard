<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // --- 1) Drop problematic tables safely (if they exist) ---
        // Drop FKs first to avoid errors.
        if (Schema::hasTable('order_confirmations')) {
            // Try to drop any known FKs referencing orders/order_confirmations
            try {
                Schema::table('order_confirmations', function (Blueprint $table) {
                    if (Schema::hasColumn('order_confirmations', 'order_id')) {
                        // FK name may vary; drop by column
                        $table->dropForeign(['order_id']);
                    }
                });
            } catch (\Throwable $e) {
                // ignore if FK didn’t exist
            }

            Schema::dropIfExists('order_confirmations');
        }

        // If you also created other experimental tables you want to remove, repeat:
        // Schema::dropIfExists('matches');
        // Schema::dropIfExists('processing_tasks');
        // Schema::dropIfExists('exceptions');

        // --- 2) Extend orders table richly ---
        Schema::table('orders', function (Blueprint $table) {
            // Identity
            if (!Schema::hasColumn('orders', 'order_number')) {
                $table->string('order_number')->unique()->after('id');
            }

            // Parties
            if (!Schema::hasColumn('orders', 'supplier_code')) {
                $table->string('supplier_code')->nullable()->after('order_number');
            }
            if (!Schema::hasColumn('orders', 'supplier')) {
                $table->string('supplier')->nullable()->after('supplier_code');
            }
            if (!Schema::hasColumn('orders', 'customer_name')) {
                $table->string('customer_name')->nullable()->after('supplier');
            }
            if (!Schema::hasColumn('orders', 'customer_email')) {
                $table->string('customer_email')->nullable()->after('customer_name');
            }

            // Dates / lifecycle
            if (!Schema::hasColumn('orders', 'order_date')) {
                $table->date('order_date')->nullable()->after('customer_email');
            }
            if (!Schema::hasColumn('orders', 'required_by')) {
                $table->date('required_by')->nullable()->after('order_date');
            }
            if (!Schema::hasColumn('orders', 'expected_delivery_date')) {
                $table->date('expected_delivery_date')->nullable()->after('required_by');
            }
            if (!Schema::hasColumn('orders', 'closed_at')) {
                $table->timestamp('closed_at')->nullable()->after('expected_delivery_date');
            }
            if (!Schema::hasColumn('orders', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('closed_at');
            }

            // Financials
            if (!Schema::hasColumn('orders', 'currency')) {
                $table->string('currency', 3)->default('EUR')->after('cancelled_at');
            }
            if (!Schema::hasColumn('orders', 'subtotal_amount')) {
                $table->decimal('subtotal_amount', 12, 2)->nullable()->after('currency');
            }
            if (!Schema::hasColumn('orders', 'tax_amount')) {
                $table->decimal('tax_amount', 12, 2)->nullable()->after('subtotal_amount');
            }
            if (!Schema::hasColumn('orders', 'total_amount')) {
                $table->decimal('total_amount', 12, 2)->nullable()->after('tax_amount');
            }
            if (!Schema::hasColumn('orders', 'payment_terms')) {
                $table->string('payment_terms')->nullable()->after('total_amount');
            }
            if (!Schema::hasColumn('orders', 'incoterm')) {
                $table->string('incoterm', 16)->nullable()->after('payment_terms');
            }

            // Logistics
            if (!Schema::hasColumn('orders', 'shipping_method')) {
                $table->string('shipping_method')->nullable()->after('incoterm');
            }
            if (!Schema::hasColumn('orders', 'tracking_number')) {
                $table->string('tracking_number')->nullable()->after('shipping_method');
            }

            // Addresses (JSON)
            if (!Schema::hasColumn('orders', 'billing_address')) {
                $table->json('billing_address')->nullable()->after('tracking_number');
            }
            if (!Schema::hasColumn('orders', 'delivery_address')) {
                $table->json('delivery_address')->nullable()->after('billing_address');
            }

            // Status & source
            if (!Schema::hasColumn('orders', 'status')) {
                $table->enum('status', ['open', 'in_progress', 'closed', 'cancelled'])
                      ->default('open')->after('delivery_address');
            }
            if (!Schema::hasColumn('orders', 'source')) {
                $table->string('source')->nullable()->after('status'); // portal/email/edi/etc
            }
            if (!Schema::hasColumn('orders', 'channel')) {
                $table->string('channel')->nullable()->after('source');
            }

            // Misc
            if (!Schema::hasColumn('orders', 'po_number')) {
                $table->string('po_number')->nullable()->after('channel');
            }
            if (!Schema::hasColumn('orders', 'tags')) {
                $table->json('tags')->nullable()->after('po_number');
            }
            if (!Schema::hasColumn('orders', 'metadata')) {
                $table->json('metadata')->nullable()->after('tags');
            }
            if (!Schema::hasColumn('orders', 'notes')) {
                $table->text('notes')->nullable()->after('metadata');
            }
        });
    }

    public function down(): void
    {
        // Reverse only the added columns on orders
        Schema::table('orders', function (Blueprint $table) {
            $drops = [
                'order_number','supplier_code','supplier','customer_name','customer_email',
                'order_date','required_by','expected_delivery_date','closed_at','cancelled_at',
                'currency','subtotal_amount','tax_amount','total_amount','payment_terms','incoterm',
                'shipping_method','tracking_number','billing_address','delivery_address',
                'status','source','channel','po_number','tags','metadata','notes',
            ];
            foreach ($drops as $col) {
                if (Schema::hasColumn('orders', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        // We won’t recreate the dropped tables in down() to avoid
        // accidental data resurrection. If you want, you can add
        // Schema::create(...) here for order_confirmations again.
    }
};
