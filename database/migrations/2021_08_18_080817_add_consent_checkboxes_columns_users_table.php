<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddConsentCheckboxesColumnsUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('consent_terms_conditions')->default(1)->after('set_profile_picture_reminder_at')->comment("1 if terms and conditions checked - for both mobile and portal");
            $table->boolean('consent_data')->default(1)->after('consent_terms_conditions')->comment("1 if data transfer summary checked - for portal initially");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('consent_terms_conditions');
            $table->dropColumn('consent_data');
        });
    }
}
