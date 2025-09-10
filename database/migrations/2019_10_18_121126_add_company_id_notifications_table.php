<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCompanyIdNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('notifications', function (Blueprint $table) {
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
        if (Schema::hasTable('notifications')) {
            Schema::table('notifications', function (Blueprint $table) {
                if (Schema::hasColumn('notifications', 'company_id')) {
                    $table->dropForeign('notifications_company_id_foreign');
                    $table->dropColumn('company_id');
                }
            });
        }
        Schema::enableForeignKeyConstraints();
    }
}
