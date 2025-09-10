<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBroadcastCompanyIdColumnToGroupMessages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('group_messages', function (Blueprint $table) {
            $table->unsignedBigInteger('broadcast_company_id')->nullable()->comment("refers to companies table")->after('is_broadcast');

            // adding cardinalities
            $table->foreign('broadcast_company_id')
                ->references('id')
                ->on('companies')
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
        Schema::table('group_messages', function (Blueprint $table) {
            $table->dropForeign('group_messages_broadcast_company_id_foreign');
            $table->dropColumn('broadcast_company_id');
        });
        Schema::enableForeignKeyConstraints();
    }
}
