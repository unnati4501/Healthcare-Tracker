<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateApiLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('api_logs', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of current table");
            
            $table->unsignedBigInteger('user_id')->nullable()->comment("refers to users table");

            $table->string('type', 255)->nullable()->comment("type of log request or response");
            $table->text('route')->nullable()->comment("route of api");
            $table->longText('headers')->nullable()->comment("headers of api request");
            $table->longText('request_data')->nullable()->comment("api request data");
            $table->longText('parameters')->nullable()->comment("api query parameters");
            $table->longText('response_data')->nullable()->comment("api response data");
            $table->string('status', 255)->nullable()->comment("response status code");
            
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
        Schema::dropIfExists('api_logs');
        Schema::enableForeignKeyConstraints();
    }
}
