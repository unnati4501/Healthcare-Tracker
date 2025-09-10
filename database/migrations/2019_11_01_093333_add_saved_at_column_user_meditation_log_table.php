<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSavedAtColumnUserMeditationLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_meditation_track_logs', function (Blueprint $table) {
            $table->timestamp('saved_at')->nullable()->after('saved')->comment('store date at which user saved meditation track logs.');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::disableForeignKeyConstraints();
        if (Schema::hasTable('user_meditation_track_logs')) {
            Schema::table('user_meditation_track_logs', function (Blueprint $table) {
                if (Schema::hasColumn('user_meditation_track_logs', 'saved_at')) {
                    $table->dropColumn('saved_at');
                }
            });
        }
        Schema::enableForeignKeyConstraints();
    }
}
