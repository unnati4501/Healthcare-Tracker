<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveStatusColumnFromEventCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('event_companies', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('event_companies', function (Blueprint $table) {
            $table->enum('status', [1, 2, 3, 4, 5])->comment("1 => draft, 2 => published, 3 => cancelled, 4 => booked, 5 => completed");
        });
    }
}
