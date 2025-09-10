<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDigitalTherapySpecificTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('digital_therapy_specific', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('location_id')->nullable()->comment("refers to location table but it's not foreign key ");
            $table->unsignedBigInteger('company_id')->comment("refers to companies table");
            $table->unsignedBigInteger('ws_id')->comment('refers to user table');
            $table->date('date')->comment("date of the slot");
            $table->time('start_time')->comment("start time of the slot");
            $table->time('end_time')->comment("end time of the slot");
            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");
            $table
                ->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');
            $table->foreign('location_id')
                ->nullable()
                ->references('id')
                ->on('company_locations');
            $table
                ->foreign('ws_id')
                ->references('id')
                ->on('users')
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
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('digital_therapy_specific');
        Schema::enableForeignKeyConstraints();
    }
}
