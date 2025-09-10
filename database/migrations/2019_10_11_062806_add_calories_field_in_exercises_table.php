<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCaloriesFieldInExercisesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('exercises', function (Blueprint $table) {
            $table->integer('calories')->default(0)->after('show_map')->comment('default 0, ideal calories per minute');
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
                if (Schema::hasColumn('exercises', 'calories')) {
                    $table->dropColumn('calories');
                }
            });
        }
        Schema::enableForeignKeyConstraints();
    }
}
