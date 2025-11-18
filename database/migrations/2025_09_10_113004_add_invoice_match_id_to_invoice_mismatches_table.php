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
        Schema::table('invoice_mismatches', function (Blueprint $table) {
            $table->foreignId('invoice_match_id')->nullable()->constrained('invoice_matches')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_mismatches', function (Blueprint $table) {
            $table->dropForeign(['invoice_match_id']);
            $table->dropColumn('invoice_match_id');
        });
    }
};