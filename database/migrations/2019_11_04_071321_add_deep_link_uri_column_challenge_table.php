<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDeepLinkUriColumnChallengeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('challenges', function (Blueprint $table) {
            $table->string('deep_link_uri')->nullable()->after('recurring_completed')->comment('represents the deep link which redirects users to the challenge view on app');
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
        if (Schema::hasTable('challenges')) {
            Schema::table('challenges', function (Blueprint $table) {
                if (Schema::hasColumn('challenges', 'deep_link_uri')) {
                    $table->dropColumn('deep_link_uri');
                }
            });
        }
        Schema::enableForeignKeyConstraints();
    }
}
