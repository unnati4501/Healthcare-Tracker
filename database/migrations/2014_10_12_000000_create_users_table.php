<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Primary key of the current table');
            $table->string('first_name', 255)->comment('First name of user')->index();
            $table->string('last_name', 255)->comment('Last name of user')->index();
            $table->string('email', 255)->unique()->comment('Email of user and it should be unique')->index();
            $table->string('timezone', 255)->default('Asia/Calcutta')->comment('TimeZone of user');
            $table->boolean('is_coach')->default(false)->comment('true, If user allowed to add meditation');
            $table->boolean('is_premium')->default(false)->comment('user membership status');
            $table->boolean('is_blocked')->default(false)->comment('status of user blocked or not');
            $table->boolean('can_access_app')->default(false)->comment('flag to check user can access app or not');
            $table->timestamp('email_verified_at')->nullable()->comment('Timestamp when user verifies email');
            $table->string('password')->nullable()->comment('Encrypted password of user');
            $table->dateTime('last_login_at')->nullable()->comment('latest date and time when userd logged in');
            $table->string('confirm_token', 100)->nullable();
            $table->string('dvreset_token', 100)->nullable();
            $table->boolean('is_confirmed')->default(false);
            $table->rememberToken()->comment('Remember me token');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
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
        Schema::dropIfExists('users');
        Schema::enableForeignKeyConstraints();
    }
}
