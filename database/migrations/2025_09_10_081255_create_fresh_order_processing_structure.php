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
        // Drop existing tables in correct order (respecting foreign keys)
        Schema::dropIfExists('matches');
        Schema::dropIfExists('exceptions');
        Schema::dropIfExists('order_confirmations');
        
        // Create fresh order_confirmations table as the parent entity
        Schema::create('order_confirmations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->nullable()->index();
            $table->string('supplier')->nullable();
            $table->datetime('received_at')->nullable();
            $table->decimal('confidence', 3, 2)->nullable(); // 0.00 to 1.00
            $table->json('payload')->nullable();
            $table->string('status')->default('pending')->index(); // pending, processed, failed
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->unsignedBigInteger('updated_by')->nullable()->index();
            $table->timestamps();
            
            $table->foreign('order_id')->references('id')->on('orders')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
        });

        // Create order_mismatches table (renamed from exceptions)
        Schema::create('order_mismatches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_confirmation_id')->index();
            $table->unsignedBigInteger('order_id')->nullable()->index();
            $table->string('code')->index(); // missing_lines, price_delta, unknown_supplier, doc_quality, etc.
            $table->string('severity')->index(); // low, medium, high, critical
            $table->string('status')->index(); // open, in_progress, resolved
            $table->string('message', 1000)->nullable();
            $table->json('details')->nullable(); // Additional mismatch details
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->unsignedBigInteger('resolved_by')->nullable()->index();
            $table->datetime('resolved_at')->nullable();
            $table->timestamps();
            
            $table->foreign('order_confirmation_id')->references('id')->on('order_confirmations')->cascadeOnDelete();
            $table->foreign('order_id')->references('id')->on('orders')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('resolved_by')->references('id')->on('users')->nullOnDelete();
        });

        // Create order_matches table (renamed from matches)
        Schema::create('order_matches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_confirmation_id')->index();
            $table->unsignedBigInteger('order_id')->nullable()->index();
            $table->string('po_number')->nullable();
            $table->string('customer')->nullable();
            $table->string('status')->default('pending')->index(); // pending, processed, failed
            $table->json('payload')->nullable();
            $table->unsignedBigInteger('author_id')->nullable()->index();
            $table->unsignedBigInteger('updated_by')->nullable()->index();
            $table->string('strategy')->nullable(); // exact, fuzzy, heuristic, fallback
            $table->decimal('score', 3, 2)->nullable(); // 0.00 to 1.00
            $table->string('result')->nullable(); // matched, partial, unmatched, needs_review
            $table->datetime('matched_at')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable()->index();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->foreign('order_confirmation_id')->references('id')->on('order_confirmations')->cascadeOnDelete();
            $table->foreign('order_id')->references('id')->on('orders')->nullOnDelete();
            $table->foreign('author_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('reviewed_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_matches');
        Schema::dropIfExists('order_mismatches');
        Schema::dropIfExists('order_confirmations');
    }
};