<?php

use App\Http\Traits\DisableForeignKeys;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeyZcSurveySettingsTable extends Migration
{
    use DisableForeignKeys;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Disable foreign key checks!
        $this->disableForeignKeys();

        Schema::table('zc_survey_settings', function (Blueprint $table) {
            $table->foreign('survey_id')
                ->references('id')
                ->on('zc_survey')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');
        });

        // Enable foreign key checks!
        $this->enableForeignKeys();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Disable foreign key checks!
        $this->disableForeignKeys();

        Schema::table('zc_survey_settings', function (Blueprint $table) {
            $table->dropForeign('zc_survey_settings_survey_id_foreign');
        });

        // Enable foreign key checks!
        $this->enableForeignKeys();
    }
}
