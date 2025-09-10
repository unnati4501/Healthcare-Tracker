<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCompanyIdInFeedTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('feeds', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->index('company_id')->nullable()->comment("refers to companies table - company id of creator_id")->after('creator_id');
            $table->foreign('company_id')->references('id')->on('companies')->onUpdate('CASCADE')->onDelete('CASCADE');
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
        if (Schema::hasTable('feeds')) {
            Schema::table('feeds', function (Blueprint $table) {
                $table->dropForeign('feeds_company_id_foreign');
                $table->dropColumn('company_id');
            });
        }
        Schema::enableForeignKeyConstraints();
    }
}
