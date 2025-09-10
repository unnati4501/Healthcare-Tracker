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
        Schema::table('cronofy_schedule', function (Blueprint $table) {
            $table->integer('score')->default(0)->comment("Score added to session after complete each session")->after('no_show');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cronofy_schedule', function (Blueprint $table) {
            $table->dropColumn('score');
        });
    }
};
