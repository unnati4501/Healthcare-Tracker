<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDecriptionFieldInContentChallengeInTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('content_challenge', function (Blueprint $table) {
            $table->text('description')->nullable()->comment('description')->after('category');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('content_challenge', function (Blueprint $table) {
            $table->dropColumn(['description']);
        });
    }
}
