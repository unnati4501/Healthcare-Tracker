<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateLanguageFieldInUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('language', 150)->default(1)->after('avg_steps')->comment("Language for WS 1=>'English',2=>'Polish',3=>'Portuguese',4=>'Spanish',5=>'French',6=>'German',7=>'Italian'")->change();
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
            $table->unsignedSmallInteger('language')->default(1)->after('avg_steps')->comment("Language for WS 1=>'English',2=>'Polish',3=>'Portuguese',4=>'Spanish',5=>'French',6=>'German',7=>'Italian'")->change();
        });
    }
}
