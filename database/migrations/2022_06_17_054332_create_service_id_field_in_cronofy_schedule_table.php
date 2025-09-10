<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServiceIdFieldInCronofyScheduleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cronofy_schedule', function (Blueprint $table) {
            $table->unsignedBigInteger('service_id')->nullable()->comment("refers to service table")->after('ws_id');
            $table->unsignedBigInteger('topic_id')->nullable()->comment("refers to  table")->after('service_id');

            $table->foreign('service_id')
                ->references('id')->on('services')
                ->onDelete('cascade');
            $table->foreign('topic_id')
                ->references('id')->on('service_sub_categories')
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
        Schema::table('cronofy_schedule', function (Blueprint $table) {
            $table->dropForeign('cronofy_schedule_service_id_foreign');
            $table->dropForeign('cronofy_schedule_topic_id_foreign');
            $table->dropColumn(['service_id', 'topic_id']);
        });
        Schema::enableForeignKeyConstraints();
    }
}
