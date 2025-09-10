<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePermissionRoleTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('permission_role', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('permission_id')->comment("refers to permissions table");
            $table->unsignedBigInteger('role_id')->comment("refers to roles table");
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->timestamps();
        });

        Schema::table('permission_role', function ($table) {
            $table->foreign('permission_id')
                ->references('id')->on('permissions')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');
            $table->foreign('role_id')
                ->references('id')->on('roles')
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
        Schema::drop('permission_role');
    }
}
