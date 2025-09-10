<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWsUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ws_user', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of the current table");
            $table->unsignedBigInteger('user_id')->nullable()->comment("refers to user table");
            $table->string('language', 150)->default(1)->comment("Language for WS 1=>'English',2=>'Polish',3=>'Portuguese',4=>'Spanish',5=>'French',6=>'German',7=>'Italian'");
            $table->unsignedSmallInteger('conferencing_mode')->default(1)->comment("Video Conferencing mode for WS 1=>WhereBy, 2=>Custom");
            $table->string('video_link', 250)->nullable()->comment('video link for conferencing mode of ws');
            $table->unsignedSmallInteger('shift')->default(1)->comment("shift for WS 1=>'Morning',2=>'Evening'");
            $table->text('years_of_experience')->nullable()->comment("years of experience is for ws");
            $table->boolean('is_profile')->default(false)->comment("WS user profile should updated or not");
            $table->boolean('is_authenticate')->default(false)->comment("this is fields is use for WS have set calendar or not");
            $table->boolean('is_availability')->default(false)->comment("this is fields is use for WS have set availability or not");
            $table->boolean('is_cronofy')->default(false)->comment("WS user set his whole details or not.");
            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");

            $table->foreign('user_id')
                ->references('id')->on('users')
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
        Schema::dropIfExists('ws_user');
        Schema::enableForeignKeyConstraints();
    }
}
