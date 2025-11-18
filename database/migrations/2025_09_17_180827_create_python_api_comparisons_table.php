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
        Schema::create('python_api_comparisons', function (Blueprint $table) {
            $table->id();
            
            // Order information
            $table->string('order_number')->nullable();
            $table->string('ab_number')->nullable();
            $table->string('customer_number')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('commission')->nullable();
            
            // Comparison results
            $table->json('order_header')->nullable();
            $table->json('ab_header')->nullable();
            $table->json('header_comparison')->nullable();
            $table->json('items_comparison')->nullable();
            $table->json('summary')->nullable();
            $table->json('full_payload')->nullable();
            
            // Status and metadata
            $table->string('overall_status')->default('pending'); // pending, matched, mismatched, needs_review
            $table->integer('total_items')->default(0);
            $table->integer('matches')->default(0);
            $table->integer('mismatches')->default(0);
            $table->integer('review_required')->default(0);
            $table->integer('missing_in_order')->default(0);
            $table->integer('missing_in_confirmation')->default(0);
            
            // Timestamps
            $table->timestamp('processed_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            
            $table->timestamps();
            
            // Indexes
            $table->index(['order_number', 'ab_number']);
            $table->index('overall_status');
            $table->index('processed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('python_api_comparisons');
    }
};
