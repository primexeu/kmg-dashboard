<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop existing invoice-related tables if they exist
        Schema::dropIfExists('invoice_mismatches');
        Schema::dropIfExists('invoice_matches');
        
        // Create invoice_mismatches table
        Schema::create('invoice_mismatches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
            $table->string('mismatch_type'); // 'price', 'quantity', 'item', 'date', 'other'
            $table->text('description');
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->enum('status', ['open', 'investigating', 'resolved', 'closed'])->default('open');
            $table->json('details')->nullable(); // Store mismatch details
            $table->foreignId('reported_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('resolved_at')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->timestamps();
            
            $table->index(['invoice_id', 'status']);
            $table->index(['severity', 'status']);
        });

        // Create invoice_matches table
        Schema::create('invoice_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
            $table->string('invoice_number');
            $table->string('supplier');
            $table->enum('status', ['pending', 'processing', 'matched', 'mismatched', 'completed'])->default('pending');
            $table->enum('result', ['matched', 'mismatched', 'partial', 'error'])->default('matched');
            $table->timestamp('matched_at')->nullable();
            $table->decimal('confidence_score', 5, 4)->nullable(); // 0.0000 to 1.0000
            $table->json('payload')->nullable(); // Store comparison results
            $table->foreignId('author_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            $table->index(['invoice_id', 'status']);
            $table->index(['result', 'matched_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_matches');
        Schema::dropIfExists('invoice_mismatches');
    }
};