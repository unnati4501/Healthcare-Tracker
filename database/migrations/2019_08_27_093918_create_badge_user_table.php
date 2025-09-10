<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBadgeUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('badge_user', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Primary key of the current table');

            $table->unsignedBigInteger('badge_id')->comment("refers to badges table");
            $table->unsignedBigInteger('user_id')->comment("refers to users table");
            
            $table->enum('status', ['Active', 'Expired'])->default('Active')->comment('specifies status of badge for user. It can be Active or Expired');
            $table->timestamp('expired_at')->nullable()->comment('datetime when badge expired for the user');

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();

            $table->foreign('badge_id')
                ->references('id')->on('badges')
                ->onDelete('cascade');

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
        Schema::dropIfExists('badge_user');
        Schema::enableForeignKeyConstraints();
    }
}
