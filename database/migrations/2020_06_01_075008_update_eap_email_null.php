<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateEapEmailNull extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('eap_list', function (Blueprint $table) {
            $table->string('email', 255)->comment('email of EAP list')->nullable()->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('eap_list', function (Blueprint $table) {
            $table->string('email', 255)->comment('email of EAP list')->change();
        });
    }
}
