<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewFieldHelpOptionsCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->boolean('is_intercom')->default(true)->comment('true, if company has enabled zendesk functionality and use in app side')->change();
            $table->boolean('is_faqs')->default(true)->after('is_intercom')->comment('true, if comapny has enabled faq functionality and use in app side');
            $table->boolean('is_eap')->default(true)->after('is_faqs')->comment('true, if comapny has enabled EAP functionality and use in app side');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->boolean('is_intercom')->default(true)->comment('true, if company has enabled zendesk functionality and use in app side')->change();
            $table->dropColumn('is_faqs');
            $table->dropColumn('is_eap');
        });
    }
}
