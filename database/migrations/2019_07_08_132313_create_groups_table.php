<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('groups', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of the current table");

            $table->unsignedBigInteger('creator_id')->comment("refers to users table- creator of the group");
            $table->unsignedBigInteger('category_id')->comment("refers to categories table");
            
            $table->string('expertise_level', 255)->nullable()->comment('user expertise level for the current category - visiblity purpose of group to users');
            $table->string('title', 255)->comment('group title');
            $table->text('description')->nullable()->comment('group description');
            $table->text('motive')->nullable()->comment('group motive');
            $table->text('who_can_join')->nullable()->comment('terms - who can join the group');
            $table->string('deep_link_uri')->comment('represents the deep link which redirects users to the group view on app');
            
            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");

            $table->foreign('creator_id')
                ->references('id')->on('users')
                ->onDelete('cascade');
            $table->foreign('category_id')
                ->references('id')->on('categories')
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
        Schema::dropIfExists('groups');
        Schema::enableForeignKeyConstraints();
    }
}
