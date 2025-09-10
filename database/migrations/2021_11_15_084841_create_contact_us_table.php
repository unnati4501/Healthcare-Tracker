<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContactUsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contact_us', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Primary key of the current table');
            $table->unsignedBigInteger('user_id')->nullable()->comment("refers to users table");
            $table->string('name', 255)->comment('Name of user');
            $table->string('email', 255)->comment('Email of user');
            $table->text('description');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::enableForeignKeyConstraints();
        Schema::dropIfExists('contact_us');
        Schema::disableForeignKeyConstraints();
    }
}
