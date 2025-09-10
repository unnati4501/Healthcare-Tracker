<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePermissionsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('parent_id')->unsigned()->index('parent_id')->nullable();
            $table->string('name', 191)->unique()->comment('title of permission ex: add/edit');
            $table->string('display_name', 191)->comment('display name for permission ex: adduser');
            $table->smallInteger('sort')->unsigned()->default(0);
            $table->boolean('status')->default(1)->comment('1 => Active, 0 => Inactive curreent status of permission');
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('permissions');
    }
}
