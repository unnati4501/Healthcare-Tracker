<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCronofyAuthenticateCode extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cronofy_authenticate_code', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of the current table");
            $table->unsignedBigInteger('user_id')->nullable()->comment("refers to user table");
            $table->text('access_token')->comment("Authenticate access token, will be get from the cronofy APIs");
            $table->text('refresh_token')->comment("Authenticate refresh token, will be get from the cronofy APIs");
            $table->text('expires_in')->comment("Authenticate refresh token, will be get from the cronofy APIs");
            $table->text('sub_id')->comment("Authenticate sub account id, will be get from the cronofy APIs");
            $table->text('profile_name')->comment("Authenticate profile name of email address, will be get from the cronofy APIs");
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
        Schema::dropIfExists('cronofy_authenticate_code');
        Schema::enableForeignKeyConstraints();
    }
}
