<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTotalStepsFieldsInMapLibraryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('map_library', function (Blueprint $table) {
            $table->string('total_steps')->nullable()->after('total_distance')->comment("number of total steps");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('map_library', function (Blueprint $table) {
            $table->dropColumn('total_steps');
        });
    }
}
