<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGroupMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('group_members', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of the current table");

            $table->unsignedBigInteger('user_id')->comment("refers to users table- member of the group");
            $table->unsignedBigInteger('group_id')->comment("refers to groups table");
            
            $table->string('status', 255)->comment('status of the user in group');
            $table->dateTime('joined_date')->comment('date time when user joined the group');
            $table->dateTime('left_date')->nullable()->comment('date time when user left the group');
            
            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('cascade');
            $table->foreign('group_id')
                ->references('id')->on('groups')
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
        Schema::dropIfExists('group_members');
        Schema::enableForeignKeyConstraints();
    }
}
