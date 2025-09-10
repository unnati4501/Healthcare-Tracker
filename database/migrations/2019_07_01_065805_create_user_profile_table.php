<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserProfileTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_profile', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("Primary key of the table");
            
            $table->unsignedBigInteger('user_id')->comment("refers to users table");
            
            $table->string('gender')->nullable()->comment("gender of user male/female");
            $table->double('height')->nullable()->comment("height of user in centimeter");
            $table->integer('age')->comment("user age on current date");
            $table->date('birth_date')->nullable()->comment("date of birth in Y-m-d");
            $table->text('about')->nullable()->comment("information about the user");

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
        Schema::dropIfExists('user_profile');
        Schema::enableForeignKeyConstraints();
    }
}
