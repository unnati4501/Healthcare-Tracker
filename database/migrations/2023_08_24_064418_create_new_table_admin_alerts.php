<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNewTableAdminAlerts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_alerts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title')->nullable()->comment('Alert title');
            $table->longText('description')->nullable()->comment('Alert description');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admin_alerts');
    }
}
