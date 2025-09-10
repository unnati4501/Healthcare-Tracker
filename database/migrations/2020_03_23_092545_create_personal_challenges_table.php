<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePersonalChallengesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('personal_challenges', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of current table");
            $table->unsignedBigInteger('creator_id')->comment("refers to users table - creator of the challenge");
            $table->unsignedBigInteger('company_id')->nullable()->comment("refers to companies table");
            $table->string('logo', 255)->nullable()->comment('personal challenge logo');
            $table->string('title', 255)->comment("title of the personal challenge");
            $table->float('duration', 15, 2)->comment('duration of the personal challenge');
            $table->enum('type', ['to-do', 'streak'])->default('to-do');
            $table->string('description', 255)->nullable()->comment("description for the personal challenge");
            $table->string('deep_link_uri')->nullable()->comment('represents the deep link which redirects users to the personal challenge view on app');
            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");

            $table->foreign('creator_id')
                ->references('id')
                ->on('users')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');

            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('personal_challenges');
    }
}
