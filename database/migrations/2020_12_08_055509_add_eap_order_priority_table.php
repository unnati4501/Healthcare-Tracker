<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEapOrderPriorityTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('eap_order_priority', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Primary key of the table');
            $table->unsignedBigInteger('eap_id')->nullable()->comment("refers to the eap_list table");
            $table->unsignedBigInteger('company_id')->nullable()->comment("refers to the companies table");
            $table->integer('order_priority')->default(0)->comment("default 0, flag is for order priority");
            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");

            $table->foreign('eap_id')
                ->references('id')
                ->on('eap_list')
                ->onDelete('CASCADE');

            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
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
        Schema::dropIfExists('eap_order_priority');
        Schema::enableForeignKeyConstraints();
    }
}
