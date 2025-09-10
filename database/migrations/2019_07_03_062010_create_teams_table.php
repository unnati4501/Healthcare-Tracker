<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTeamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('teams', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of the current table");

            $table->unsignedBigInteger('company_id')->comment("refers to companies table");
            $table->unsignedBigInteger('department_id')->comment("refers to departments table");

            $table->char('code', 6)->unique()->comment("unique code of the team which should of 6 characters");
            $table->string('name', 255)->comment("name of the team");
            $table->boolean('default')->default(false)->comment('1 => default, 0 => not default, flag for default team');

            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");

            $table->foreign('company_id')
                ->references('id')->on('companies')
                ->onDelete('cascade');
            $table->foreign('department_id')
                ->references('id')->on('departments')
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
        Schema::dropIfExists('teams');
        Schema::enableForeignKeyConstraints();
    }
}
