<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsAccessedFieldToConsentFromLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('consent_form_logs', function (Blueprint $table) {
            $table->boolean('is_accessed')->default(0)->comment('Accessed kin details => 1 and Not Accessed => 0 ')->after('contact_no');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('consent_form_logs', function (Blueprint $table) {
            $table->dropColumn('is_accessed');
        });
    }
}
