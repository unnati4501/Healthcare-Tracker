<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeColumnToAppSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('app_settings', function (Blueprint $table) {
            $table->string('value', 255)->nullable()->after('key')->comment("value for the field can be null")->change();
            $table->string('type', 255)->nullable()->after('value')->comment("type for the field like textbox, radio, file");
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
        if (Schema::hasTable('app_settings')) {
            Schema::table('app_settings', function (Blueprint $table) {
                if (Schema::hasColumn('app_settings', 'type')) {
                    $table->dropColumn('type');
                }
            });
        }
        Schema::enableForeignKeyConstraints();
    }
}
