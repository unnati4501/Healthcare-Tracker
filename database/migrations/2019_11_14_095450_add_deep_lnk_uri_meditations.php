<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeepLnkUriMeditations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('meditation_tracks', function (Blueprint $table) {
            $table->string('deep_link_uri')->nullable()->after('is_premium')->comment('represents the deep link which redirects users to the meditation track view on app');
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
        if (Schema::hasTable('meditation_tracks')) {
            Schema::table('meditation_tracks', function (Blueprint $table) {
                if (Schema::hasColumn('meditation_tracks', 'deep_link_uri')) {
                    $table->dropColumn('deep_link_uri');
                }
            });
        }
        Schema::enableForeignKeyConstraints();
    }
}
