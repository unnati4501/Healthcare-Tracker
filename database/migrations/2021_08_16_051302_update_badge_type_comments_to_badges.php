<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateBadgeTypeCommentsToBadges extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('badges', function (Blueprint $table) {
            $table->string('type', 255)->comment('badge type - challenge, general, masterclass, daily')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('badges', function (Blueprint $table) {
            $table->string('type', 255)->comment("badge type - challenge, general, course")->change();
        });
    }
}
