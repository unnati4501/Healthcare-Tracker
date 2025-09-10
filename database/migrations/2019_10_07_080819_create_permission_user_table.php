<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePermissionUserTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('permission_user', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('permission_id')->comment('id from permission table');
            $table->unsignedBigInteger('user_id')->comment('id from user table');
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->timestamps();
        });

        Schema::table('permission_user', function ($table) {
            $table->foreign('permission_id')
                ->references('id')->on('permissions')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');
            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('permission_user');
    }
}
