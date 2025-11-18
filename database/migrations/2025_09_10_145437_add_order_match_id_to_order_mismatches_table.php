<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_mismatches', function (Blueprint $table) {
            $table->foreignId('order_match_id')->nullable()->constrained('order_matches')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('order_mismatches', function (Blueprint $table) {
            $table->dropForeign(['order_match_id']);
            $table->dropColumn('order_match_id');
        });
    }
};