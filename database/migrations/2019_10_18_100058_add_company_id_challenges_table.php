<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCompanyIdChallengesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('challenges', function (Blueprint $table) {
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
        if (Schema::hasTable('challenges')) {
            Schema::table('challenges', function (Blueprint $table) {
                if (Schema::hasColumn('challenges', 'company_id')) {
                    $table->dropForeign('challenges_company_id_foreign');
                    $table->dropColumn('company_id');
                }
            });
        }
        Schema::enableForeignKeyConstraints();
    }
}
