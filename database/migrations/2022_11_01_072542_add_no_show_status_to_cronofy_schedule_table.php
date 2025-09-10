<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNoShowStatusToCronofyScheduleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cronofy_schedule', function (Blueprint $table) {
            $table->enum('no_show', ['No', 'Yes'])->default('No')->after('status')->comment('No : session is completed , yes : session ongoing');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cronofy_schedule', function (Blueprint $table) {
            $table->dropColumn('no_show');
        });
    }
}
