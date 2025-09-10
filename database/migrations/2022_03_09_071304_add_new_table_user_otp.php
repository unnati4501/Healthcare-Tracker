<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewTableUserOtp extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('user_otp', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of current table");
            $table->string('email', 255)->nullable()->comment("Email of user");
            $table->string('single_use_code', 255)->nullable()->comment("User OTP");
            $table->timestamp('created_at')->useCurrent()->comment("date and time when otp is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when otp is updated");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::dropIfExists('user_otp');
    }
}
