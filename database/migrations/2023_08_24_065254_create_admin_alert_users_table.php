<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminAlertUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_alert_users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('alert_id')->comment("refers to admin_alerts table");
            $table->string('user_name')->nullable()->comment('User name to which notification send');
            $table->string('user_email')->nullable()->comment('User email to which notification send');
            $table->timestamps();

            $table->foreign('alert_id')
            ->references('id')->on('admin_alerts')
            ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admin_alert_users');
    }
}
