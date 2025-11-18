<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete(); // link to Order
            $table->string('supplier')->nullable();
            $table->date('issued_at')->nullable();
            $table->date('due_at')->nullable();

            $table->decimal('subtotal_amount', 12, 2)->nullable();
            $table->decimal('tax_amount', 12, 2)->nullable();
            $table->decimal('total_amount', 12, 2)->nullable();
            $table->string('currency', 3)->default('EUR');

            $table->enum('status', ['draft','pending','approved','paid','void'])->default('pending');

            // optional file (PDF)
            $table->string('pdf_path')->nullable();

            // who created / last updated (optional but handy)
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
