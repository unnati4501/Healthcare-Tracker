<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSavedAtColumnFeedUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('feed_user', function (Blueprint $table) {
            $table->timestamp('saved_at')->nullable()->after('saved')->comment('store date at which user saved feed.');
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
        if (Schema::hasTable('feed_user')) {
            Schema::table('feed_user', function (Blueprint $table) {
                if (Schema::hasColumn('feed_user', 'saved_at')) {
                    $table->dropColumn('saved_at');
                }
            });
        }
        Schema::enableForeignKeyConstraints();
    }
}
