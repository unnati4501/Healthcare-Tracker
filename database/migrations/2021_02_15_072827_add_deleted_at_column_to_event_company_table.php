<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeletedAtColumnToEventCompanyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('event_companies', function (Blueprint $table) {
            $table->softDeletes()->after('status')->comment('date and time when record is deleted.');
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
            $table->dropSoftDeletes();
        });
    }
}
