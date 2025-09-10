<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCompanyIdToEapTickets extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('eap_tickets', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->nullable()->comment("refers to the companies table");
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
        Schema::table('eap_tickets', function (Blueprint $table) {
            $table->dropForeign('eap_tickets_company_id_foreign');
            $table->dropColumn('company_id');
        });
        Schema::enableForeignKeyConstraints();
    }
}
