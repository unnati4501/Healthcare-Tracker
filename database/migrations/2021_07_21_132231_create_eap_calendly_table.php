<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEapCalendlyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('eap_calendly', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 255)->comment('name of the calendly event');
            $table->unsignedBigInteger('user_id')->index('user_id')->comment("refers to users table");
            $table->unsignedBigInteger('therapist_id')->index('therapist_id')->comment("refers to users table");
            $table->string('event_identifier', 255)->comment('unique event url');
            $table->dateTime('start_time')->comment('start date and time of event');
            $table->dateTime('end_time')->comment('end date and time of event');
            $table->string('location', 255)->nullable()->comment('location of event');
            $table->text('notes')->comment('text of the event');
            $table->string('cancel_url', 255)->comment('cancel url of the event');
            $table->string('reschedule_url', 255)->comment('reschedule url of the event');
            $table->dateTime('event_created_at')->comment('end date and time of event');
            $table->string('cancelled_by', 255)->nullable()->comment('cancelled by if cancelled');
            $table->dateTime('cancelled_at')->nullable()->comment('cancelled date if cancelled');
            $table->text('cancelled_reason')->nullable()->comment('cancelled reason if cancelled');
            $table->string('status')->comment('status of the event');
            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');
            $table->foreign('therapist_id')
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
        Schema::dropIfExists('eap_calendly');
        Schema::enableForeignKeyConstraints();
    }
}
