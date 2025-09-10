<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewFieldSessionUpdateCompanyDigitalTherapyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('company_digital_therapy', function (Blueprint $table) {
            $table->dropColumn('dt_session_cancellation');
            $table->dropColumn('dt_session_reschedule');
            $table->bigInteger('dt_session_update')->comment('After this time session will reschedule')->after('dt_wellbeing_sp_ids')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('company_digital_therapy', function (Blueprint $table) {
            $table->dropColumn('dt_session_update');
            $table->bigInteger('dt_session_cancellation')->after('dt_wellbeing_sp_ids')->default(0);
            $table->bigInteger('dt_session_reschedule')->after('dt_session_cancellation')->default(0);
        });
    }
}
