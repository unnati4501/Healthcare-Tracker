<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIndividualSpTempTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('indUserPointListTable', function (Blueprint $table) {
            $table->bigInteger('tchID');
            $table->bigInteger('tUserId');
            $table->bigInteger('tUserTeamId');
            $table->double('tpoint', 8, 2);
            $table->integer('trank')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('indUserPointListTable');
    }
}
