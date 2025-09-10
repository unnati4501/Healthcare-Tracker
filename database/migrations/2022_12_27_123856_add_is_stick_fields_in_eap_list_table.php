<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsStickFieldsInEapListTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('eap_list', function (Blueprint $table) {
            $table->boolean('is_stick')->default(0)->comment('To identify support is stick or not')->after('deep_link_uri');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('eap_list', function (Blueprint $table) {
            if (Schema::hasColumn('eap_list', 'is_stick')) {
                $table->dropColumn('is_stick');
            }
        });
    }
}
