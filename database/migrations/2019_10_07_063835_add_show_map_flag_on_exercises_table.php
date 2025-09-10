<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddShowMapFlagOnExercisesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('exercises', function (Blueprint $table) {
            $table->boolean('show_map')->default(false)->after('type')->comment('true, if exercise needs to show route image');
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
        if (Schema::hasTable('exercises')) {
            Schema::table('exercises', function (Blueprint $table) {
                if (Schema::hasColumn('exercises', 'show_map')) {
                    $table->dropColumn('show_map');
                }
            });
        }
        Schema::enableForeignKeyConstraints();
    }
}
