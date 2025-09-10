<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompanyLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company_locations', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of the current table");

            $table->unsignedBigInteger('company_id')->comment("refers to companies table");
            $table->unsignedBigInteger('country_id')->nullable()->comment("refers to countries table");
            $table->unsignedBigInteger('state_id')->nullable()->comment("refers to states table");
            $table->unsignedBigInteger('city_id')->nullable()->comment("refers to cities table");

            $table->string('name', 255)->comment('name of location');
            $table->string('address_line1', 255)->comment('address 1 ex: street name');
            $table->string('address_line2', 255)->nullable()->comment('further info of address ex: appart. no');
            $table->string('postal_code', 255)->comment('postal coe of location');
            $table->string('timezone', 255)->comment('timezone of location');
            $table->boolean('default')->comment('1 => default, 0 => not default, flag for default location');

            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");

            $table->foreign('company_id')
                ->references('id')->on('companies')
                ->onDelete('cascade');
            $table->foreign('country_id')
                ->references('id')->on('countries')
                ->onDelete('cascade');
            $table->foreign('state_id')
                ->references('id')->on('states')
                ->onDelete('cascade');
            $table->foreign('city_id')
                ->references('id')->on('cities')
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
        Schema::dropIfExists('company_locations');
        Schema::enableForeignKeyConstraints();
    }
}
