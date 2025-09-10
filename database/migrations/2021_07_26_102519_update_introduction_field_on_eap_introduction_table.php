<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateIntroductionFieldOnEapIntroductionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('eap_introduction', function (Blueprint $table) {
            $table->text('introduction')->nullable()->comment('introduction of EAP list')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('eap_introduction', function (Blueprint $table) {
            $table->text('introduction')->comment('introduction of EAP list')->change();
        });
    }
}
