<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of current table");
            
            $table->string('name', 255)->comment("name of role - display title");
            $table->string('slug', 255)->comment("slug of role");
            $table->string('group', 255)->comment("group name for the role");
            $table->string('description', 255)->comment("description for the role");

            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");
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
        Schema::dropIfExists('roles');
        Schema::enableForeignKeyConstraints();
    }
}
