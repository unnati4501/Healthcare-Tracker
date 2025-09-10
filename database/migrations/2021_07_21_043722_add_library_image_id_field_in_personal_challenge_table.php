<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLibraryImageIdFieldInPersonalChallengeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('personal_challenges', function (Blueprint $table) {
            $table->unsignedBigInteger('library_image_id')->after('logo')->nullable()->default(null)->comment("refers to challenge_image_library table");
            $table->foreign('library_image_id')->references('id')->on('challenge_image_library')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('personal_challenges', function (Blueprint $table) {
            $table->dropForeign(['library_image_id']);
            $table->dropColumn('library_image_id');
        });
    }
}
