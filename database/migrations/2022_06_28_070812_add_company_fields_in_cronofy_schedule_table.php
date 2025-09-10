<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCompanyFieldsInCronofyScheduleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cronofy_schedule', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
            $table->unsignedBigInteger('company_id')->nullable()->index('company_id')->nullable()->comment("refers to company table")->after('user_id');
            $table->boolean('is_group')->default(false)->comment("set flag for is group session or not")->after('topic_id');
            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
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
        Schema::table('cronofy_schedule', function (Blueprint $table) {
            $table->dropForeign('cronofy_schedule_company_id_foreign');
            $table->dropColumn(['company_id', 'is_group']);
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
        });
        Schema::enableForeignKeyConstraints();
    }
}
