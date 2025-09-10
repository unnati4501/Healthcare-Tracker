<?php

use App\Http\Traits\DisableForeignKeys;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEapCreatorIdDeepLinkUriTable extends Migration
{
    use DisableForeignKeys;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('eap_list', function (Blueprint $table) {
            $table->unsignedBigInteger('creator_id')->nullable()->comment("refers to users table - creator of eap")->after('id');
            $table->string('deep_link_uri')->nullable()->comment('redirect action url from click of notification')->after('description');
            $table->foreign('creator_id')
                ->references('id')->on('users')
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
        $this->disableForeignKeys();
        Schema::table('eap_list', function (Blueprint $table) {
            $table->dropForeign('eap_list_creator_id_foreign');
            $table->dropColumn('creator_id');
            $table->dropColumn('deep_link_uri');
        });
        $this->enableForeignKeys();
    }
}
