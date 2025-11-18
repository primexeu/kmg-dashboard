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
        // First, rename the table from order_matches to matches
        Schema::rename('order_matches', 'matches');
        
        // Add the missing columns that the OrderMatch model expects
        Schema::table('matches', function (Blueprint $table) {
            $table->string('po_number')->nullable()->after('id');
            $table->string('customer')->nullable()->after('po_number');
            $table->string('status')->default('pending')->after('customer');
            $table->json('payload')->nullable()->after('status');
            $table->unsignedBigInteger('author_id')->nullable()->after('payload');
            $table->unsignedBigInteger('updated_by')->nullable()->after('author_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the added columns
        Schema::table('matches', function (Blueprint $table) {
            $table->dropColumn(['po_number', 'customer', 'status', 'payload', 'author_id', 'updated_by']);
        });
        
        // Rename back to order_matches
        Schema::rename('matches', 'order_matches');
    }
};