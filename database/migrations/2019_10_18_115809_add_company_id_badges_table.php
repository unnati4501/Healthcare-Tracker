<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCompanyIdBadgesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('badges', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')
                ->after('creator_id')
                ->nullable()
                ->comment("refers to companies table");
            $table->foreign('company_id')
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
        if (Schema::hasTable('badges')) {
            Schema::table('badges', function (Blueprint $table) {
                if (Schema::hasColumn('badges', 'company_id')) {
                    $table->dropForeign('badges_company_id_foreign');
                    $table->dropColumn('company_id');
                }
            });
        }
        Schema::enableForeignKeyConstraints();
    }
}
