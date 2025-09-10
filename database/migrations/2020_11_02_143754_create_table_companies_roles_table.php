<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableCompaniesRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company_roles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->comment("refers to companies table");
            $table->unsignedBigInteger('role_id')->comment("refers to roles table");
            $table->timestamps();

            $table
                ->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table
                ->foreign('role_id')
                ->references('id')
                ->on('roles')
                ->onUpdate('cascade')
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
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('company_roles');
        Schema::enableForeignKeyConstraints();
    }
}
