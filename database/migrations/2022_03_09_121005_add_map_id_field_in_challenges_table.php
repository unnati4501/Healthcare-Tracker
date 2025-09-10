<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMapIdFieldInChallengesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('challenges', function (Blueprint $table) {
            $table->unsignedBigInteger('map_id')->nullable()->after('library_image_id')->comment("challenge related to map library");
            $table->foreign('map_id')
                ->references('id')->on('map_library')
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
        Schema::table('challenges', function (Blueprint $table) {
            $table->dropForeign('challenges_map_id_foreign');
            $table->dropColumn('map_id');
        });
        Schema::enableForeignKeyConstraints();
    }
}
