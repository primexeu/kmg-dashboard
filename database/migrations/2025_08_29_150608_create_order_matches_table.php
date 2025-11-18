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
    Schema::create('order_matches', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('order_id')->nullable()->index();
        $table->unsignedBigInteger('order_confirmation_id')->nullable()->index();

        $table->string('strategy')->nullable();      // e.g., sku_qty_total
        $table->decimal('score', 5, 2)->nullable();  // 0.00 - 100.00
        $table->string('result')->index();           // auto_matched | needs_review | rejected
        $table->timestamp('matched_at')->nullable()->index();

        $table->unsignedBigInteger('reviewed_by')->nullable()->index();
        $table->text('notes')->nullable();

        $table->timestamps();

        // optional FKs if you have orders/order_confirmations tables
        // $table->foreign('order_id')->references('id')->on('orders')->nullOnDelete();
        // $table->foreign('order_confirmation_id')->references('id')->on('order_confirmations')->nullOnDelete();
        // $table->foreign('reviewed_by')->references('id')->on('users')->nullOnDelete();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_matches');
    }
};
