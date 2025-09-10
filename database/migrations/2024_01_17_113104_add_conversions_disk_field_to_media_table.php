<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddConversionsDiskFieldToMediaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('media', function (Blueprint $table) {
            $table->text('conversions_disk')->comment('conversions_disk value')->after('disk');
            $table->text('uuid')->comment('uuid')->after('model_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('media', function (Blueprint $table) {
            $table->dropColumn('conversions_disk');
            $table->dropColumn('uuid');
        });
    }
}
