<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddResellerRelatedFieldsToCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_id')->nullable()->default(null)->comment("refers to companies table")->after('id');
            $table->boolean('is_reseller')->default(0)->comment('To identify company is reseller or not, 1 => reseller 2 => normal')->after('industry_id');
            $table->boolean('allow_app')->default(1)->comment('To identify company has access of mobile app or not, 1 => yes 2 => no')->after('is_reseller');
            $table->boolean('allow_portal')->default(0)->comment('To identify company has access of protal or not, 1 => yes 2 => no')->after('allow_app');
            $table
                ->foreign('parent_id')
                ->references('id')
                ->on('companies')
                ->onUpdate('cascade')
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
        Schema::table('companies', function (Blueprint $table) {
            $table->dropForeign('companies_parent_id_foreign');
            $table->dropColumn('parent_id');
            $table->dropColumn('is_reseller');
            $table->dropColumn('allow_app');
            $table->dropColumn('allow_portal');
        });
        Schema::enableForeignKeyConstraints();
    }
}
