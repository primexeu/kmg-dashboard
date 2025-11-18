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
    Schema::create('exceptions', function (Blueprint $table) {
        $table->id();

        $table->unsignedBigInteger('order_id')->nullable()->index();
        $table->unsignedBigInteger('order_confirmation_id')->nullable()->index();

        $table->string('code')->index();             // missing_lines | price_delta | unknown_supplier | doc_quality
        $table->string('severity')->index();         // low | medium | high
        $table->string('status')->index();           // open | in_progress | resolved

        $table->string('message', 1000)->nullable();
        $table->unsignedBigInteger('created_by')->nullable()->index();

        $table->timestamps();

        // $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exceptions');
    }
};
