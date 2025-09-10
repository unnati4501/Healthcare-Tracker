<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCronofyCalendarTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cronofy_calendar', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of the current table");
            $table->unsignedBigInteger('user_id')->nullable()->comment("refers to user table");
            $table->unsignedBigInteger('cronofy_id')->nullable()->comment("refers to cronofy_authenticate_code table");
            $table->text('provider_name')->comment("calendar provider name");
            $table->text('profile_name')->comment("calendar profile name");
            $table->text('calendar_id')->comment("calendar id of cronofy");
            $table->boolean('readonly')->default(false)->comment("set flag of calendar readonly or not");
            $table->boolean('primary')->default(false)->comment("set flag of calendar primary or not");
            $table->boolean('status')->default(1)->comment('1 => Active, 0 => Inactive');
            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('cascade');
            $table->foreign('cronofy_id')
                ->references('id')->on('cronofy_authenticate_code')
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
        Schema::dropIfExists('cronofy_calendar');
        Schema::enableForeignKeyConstraints();
    }
}
