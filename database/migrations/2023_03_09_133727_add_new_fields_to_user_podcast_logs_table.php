<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewFieldsToUserPodcastLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_podcast_logs', function (Blueprint $table) {
            $table->timestamp('saved_at')->nullable()->after('saved')->comment('store date at which user saved podcast logs.');
            $table->timestamp('favourited_at')->nullable()->after('favourited')->comment('store date at which user favourited podcast logs.');
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
        if (Schema::hasTable('user_podcast_logs')) {
            Schema::table('user_podcast_logs', function (Blueprint $table) {
                if (Schema::hasColumn('user_podcast_logs', 'saved_at')) {
                    $table->dropColumn('saved_at');
                }

                if (Schema::hasColumn('user_podcast_logs', 'favourited_at')) {
                    $table->dropColumn('favourited_at');
                }
            });
        }
        Schema::enableForeignKeyConstraints();
    }
}
