<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUserTableForSamlSsoLogin extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('saml_token')->nullable()->after('social_type')->comment('saml token derived from saml login');
            $table->text('social_id')->nullable()->after('hs_reminded_at')->comment('represents social Id of authenticated user')->change();
            DB::statement("ALTER TABLE users MODIFY social_type TINYINT COMMENT '1 -> Azure SSO login, 2 -> Google login, 3 -> Apple login, 4 -> SAML SSO login'");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('saml_token');
            $table->string('social_id')->nullable()->after('hs_reminded_at')->comment('represents azure AD Id of authenticated user')->change();
        });
    }
}
