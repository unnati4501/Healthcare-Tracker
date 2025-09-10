<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsIntercomColumnInCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->boolean('is_intercom')->default(false)->after('status')->comment('true, if company has enabled intercome functionality');
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
        if (Schema::hasTable('companies')) {
            Schema::table('companies', function (Blueprint $table) {
                if (Schema::hasColumn('companies', 'is_intercom')) {
                    $table->dropColumn('is_intercom');
                }
            });
        }
        Schema::enableForeignKeyConstraints();
    }
}
