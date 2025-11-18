<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_mismatches', function (Blueprint $table) {
            $table->string('email')->nullable()->after('details');
            $table->boolean('email_sent')->default(false)->after('email');
            $table->timestamp('email_sent_at')->nullable()->after('email_sent');
        });
    }

    public function down(): void
    {
        Schema::table('order_mismatches', function (Blueprint $table) {
            $table->dropColumn(['email', 'email_sent', 'email_sent_at']);
        });
    }
};