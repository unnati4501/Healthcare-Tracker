<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPortalTextFieldToAppSlides extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('app_slides', function (Blueprint $table) {
            $table->text('portal_content')->nullable()->after('content')->comment("content for portal slide");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('app_slides', function (Blueprint $table) {
            $table->dropColumn('portal_content');
        });
    }
}
