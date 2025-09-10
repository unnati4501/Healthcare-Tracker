<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCreatedByFieldToFeedsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('feeds', function (Blueprint $table) {
            //$table->bigInteger('creator_id')->comment('refers to users table - auther of the feed')->change();
            $table->unsignedBigInteger('created_by')->nullable()->comment("refers to users table - creater of the feed")->after('id');
            $table->foreign('created_by')
                ->references('id')->on('users')
                ->onDelete('cascade');
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
        Schema::table('feeds', function (Blueprint $table) {
                //$table->bigInteger('creator_id')->comment('refers to users table- creator of the feed')->change();
                $table->dropForeign('feeds_created_by_foreign');
                $table->dropColumn(['created_by']);
        });
        Schema::enableForeignKeyConstraints();

    }
}
