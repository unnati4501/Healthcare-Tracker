<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCategoryFiledForConsentFormTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('consent_form', function (Blueprint $table) {
            $table->boolean('category')->default(false)->after('description')->comment('1 => Online, 0=> Offline');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('consent_form', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }
}
