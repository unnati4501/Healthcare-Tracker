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
        Schema::table('cron_logs', function (Blueprint $table) {
            $table->longtext('data')->comment('Data value stored')->after('unique_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cron_logs', function (Blueprint $table) {
            $table->dropColumn('data');
        });
    }
};
