<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_confirmations', function (Blueprint $table) {
            // core fields
            $table->string('supplier')->after('id');
            $table->timestamp('received_at')->nullable()->after('supplier');
            $table->decimal('confidence', 5, 2)->nullable()->after('received_at');

            // relation to orders
            $table->foreignId('order_id')
                  ->after('confidence')
                  ->constrained('orders')
                  ->cascadeOnDelete();

            // optional raw payload
            $table->json('payload')->nullable()->after('order_id');
        });
    }

    public function down(): void
    {
        Schema::table('order_confirmations', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
            $table->dropColumn(['supplier', 'received_at', 'confidence', 'order_id', 'payload']);
        });
    }
};
