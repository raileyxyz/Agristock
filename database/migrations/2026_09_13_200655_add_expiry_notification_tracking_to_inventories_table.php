<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventories', function (Blueprint $table) {
            $table->timestamp('expiring_soon_notified_at')->nullable()->after('expiry_date');
            $table->timestamp('expired_notified_at')->nullable()->after('expiring_soon_notified_at');
        });
    }

    public function down(): void
    {
        Schema::table('inventories', function (Blueprint $table) {
            $table->dropColumn(['expiring_soon_notified_at', 'expired_notified_at']);
        });
    }
};
