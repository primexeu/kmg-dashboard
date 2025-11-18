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
        Schema::create('intake_logs', function (\Illuminate\Database\Schema\Blueprint $t) {
            $t->id();
            $t->string('idempotency_key')->unique();
            $t->string('source');
            $t->string('type');
            $t->json('body');
            $t->string('status')->default('queued'); // queued, processing, done, failed
            $t->text('error')->nullable();
            $t->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('intake_logs');
    }
};
