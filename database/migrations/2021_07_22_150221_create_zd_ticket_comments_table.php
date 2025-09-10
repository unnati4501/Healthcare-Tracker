<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateZdTicketCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('eap_ticket_comments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('ticket_id')->index('ticket_id')->comment("zendesk ticket id");
            $table->unsignedBigInteger('comment_id')->index('comment_id')->comment("zendesk comment id");
            $table->string('comment')->comment('comment body');
            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");

            // foreign keys
            $table->foreign('ticket_id')->references('id')->on('eap_tickets')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('eap_ticket_comments');
    }
}
