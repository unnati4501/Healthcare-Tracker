<?php

use App\Http\Traits\DisableForeignKeys;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSubCategoryIdMeditationTracksTable extends Migration
{
    use DisableForeignKeys;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Disable foreign key checks!
        $this->disableForeignKeys();

        Schema::table('meditation_tracks', function (Blueprint $table) {
            $table->dropForeign('meditation_tracks_meditation_category_id_foreign');
            $table->dropColumn('meditation_category_id');
        });

        Schema::table('meditation_tracks', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id')->after('id')->default(4)->comment("refers to categories table");
            $table->unsignedBigInteger('sub_category_id')->after('category_id')->nullable()->comment("refers to sub_categories table");

            $table->foreign('category_id')
                ->references('id')
                ->on('categories')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');
            $table->foreign('sub_category_id')
                ->references('id')
                ->on('sub_categories')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');
        });

        // Enable foreign key checks!
        $this->enableForeignKeys();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Disable foreign key checks!
        $this->disableForeignKeys();

        Schema::table('meditation_tracks', function (Blueprint $table) {
            $table->dropForeign('meditation_tracks_category_id_foreign');
            $table->dropForeign('meditation_tracks_sub_category_id_foreign');
            $table->dropColumn('category_id');
            $table->dropColumn('sub_category_id');
        });

        Schema::table('meditation_tracks', function (Blueprint $table) {
            $table->unsignedBigInteger('meditation_category_id')->after('id')->nullable()->comment("refers to meditation categories table");

            $table->foreign('meditation_category_id')
                ->references('id')
                ->on('meditation_categories')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');
        });

        // Enable foreign key checks!
        $this->enableForeignKeys();
    }
}
