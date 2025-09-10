<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateZdTicketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('zd_tickets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('ticket_id')->index('ticket_id')->comment("zendesk ticket id");
            $table->unsignedBigInteger('user_id')->index('user_id')->nullable()->default(null)->comment("refers to users table");
            $table->unsignedBigInteger('therapist_id')->index('therapist_id')->nullable()->default(null)->comment("refers to users table");
            $table->json('custom_fields')->nullable();
            $table->string('status')->comment('zendesk ticket status');
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
        Schema::dropIfExists('zd_tickets');
    }
}
