<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPresenterFieldInEventTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('events', function (Blueprint $table) {
            $table->boolean('is_special')->default(false)->comment('Check this is special event or not')->after('status');
            $table->string('presenter', 255)->nullable()->comment('When event created by ZCA, RSA, RCA, Here added free text presenter name.')->after('is_special');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('is_special');
            $table->dropColumn('presenter');
        });
    }
}
