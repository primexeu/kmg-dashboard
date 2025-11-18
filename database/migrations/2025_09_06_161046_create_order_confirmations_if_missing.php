<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('order_confirmations')) {
            Schema::create('order_confirmations', function (Blueprint $table) {
                $table->id();
                $table->string('supplier');
                $table->timestamp('received_at')->nullable();
                $table->decimal('confidence', 5, 2)->nullable();
                $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
                $table->json('payload')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        // Only drop if it exists (keeps down() idempotent)
        if (Schema::hasTable('order_confirmations')) {
            Schema::dropIfExists('order_confirmations');
        }
    }
};
