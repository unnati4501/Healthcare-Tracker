<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFavouriteFieldsToFeedUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('feed_user', function (Blueprint $table) {
            $table->boolean('favourited')->default(false)->comment('true, if feed is favourited by user')->after('liked');
            $table->timestamp('favourited_at')->nullable()->comment('store date at which user favourited feed.')->after('favourited');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('feed_user', function (Blueprint $table) {
            $table->dropColumn('favourited');
            $table->dropColumn('favourited_at');
        });
    }
}
