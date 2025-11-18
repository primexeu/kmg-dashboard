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
    Schema::table('documents', function (Blueprint $table) {
        $table->string('title')->after('id');
        $table->text('description')->nullable()->after('title');
        $table->string('file_path')->after('description'); // store relative path
        $table->foreignId('uploaded_by')
              ->nullable()
              ->constrained('users')
              ->nullOnDelete()
              ->after('file_path');
    });
}

public function down(): void
{
    Schema::table('documents', function (Blueprint $table) {
        $table->dropColumn(['title', 'description', 'file_path', 'uploaded_by']);
    });
}



};
