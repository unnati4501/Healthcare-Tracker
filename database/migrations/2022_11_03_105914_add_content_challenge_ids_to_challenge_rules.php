<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddContentChallengeIdsToChallengeRules extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('challenge_rules', function (Blueprint $table) {
            $table->string('content_challenge_ids')->nullable()->after('model_id')->comment('If target type is content then content challenges id will store');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('challenge_rules', function (Blueprint $table) {
            $table->dropColumn('content_challenge_ids');
        });
    }
}
