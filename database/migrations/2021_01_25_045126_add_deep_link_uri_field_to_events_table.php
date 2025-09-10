<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeepLinkUriFieldToEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('deep_link_uri')->nullable()->after('status')->comment('represents the deep link which redirects users to the events view on app');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('events')) {
            Schema::table('events', function (Blueprint $table) {
                if (Schema::hasColumn('events', 'deep_link_uri')) {
                    $table->dropColumn('deep_link_uri');
                }
            });
        }
    }
}
