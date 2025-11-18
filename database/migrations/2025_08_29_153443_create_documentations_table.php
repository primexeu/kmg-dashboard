<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documentations', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->string('slug')->unique();
            $table->string('summary', 1000)->nullable();
            $table->longText('content')->nullable();

            // publishing
            $table->boolean('is_published')->default(false)->index();
            $table->timestamp('published_at')->nullable()->index();

            // ownership / auditing (optional)
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_published', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documentations');
    }
};
