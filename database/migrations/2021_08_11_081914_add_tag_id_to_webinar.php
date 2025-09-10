<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTagIdToWebinar extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('webinar', function (Blueprint $table) {
            $table->unsignedBigInteger('tag_id')->nullable()->after('sub_category_id')->comment("refers to category_tags table");

            // cardinalaties
            $table->foreign('tag_id')->references('id')->on('category_tags')->onDelete('cascade');
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
        Schema::table('webinar', function (Blueprint $table) {
            $table->dropForeign('webinar_tag_id_foreign');
            $table->dropColumn('tag_id');
        });
        Schema::enableForeignKeyConstraints();
    }
}
