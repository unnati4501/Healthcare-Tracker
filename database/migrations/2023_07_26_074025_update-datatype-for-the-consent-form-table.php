<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateDatatypeForTheConsentFormTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('consent_form', function (Blueprint $table) {
            $table->integer('category')->default(2)->change()->comment("1 => Online 2 => Offline");
            DB::statement("Update consent_form set category = 2 where category = 0");
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
            $table->boolean('category')->default(false)->after('description')->change();
            DB::statement("Update consent_form set category = 0 where category = 2");
        });
    }
}
