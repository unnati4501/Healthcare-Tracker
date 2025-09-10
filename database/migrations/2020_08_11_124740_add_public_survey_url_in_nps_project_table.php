<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPublicSurveyUrlInNpsProjectTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('nps_project', function (Blueprint $table) {
            $table->text('public_survey_url')->after('status')->nullable()->comment("refers to public survey url");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('nps_project', function (Blueprint $table) {
            $table->dropColumn('public_survey_url');
        });
    }
}
