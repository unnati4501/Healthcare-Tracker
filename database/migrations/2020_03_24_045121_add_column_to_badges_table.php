<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnToBadgesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('badges', function (Blueprint $table) {
            $table->boolean('is_default')->default(0)->comment('1 => default Badges which can not be deleted, 0 => Common badges which can be deleted')->after('model_name');

            $table->string('challenge_type_slug', 255)->nullable()->comment('slug for the challenge type')->after('is_default');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('badges', function (Blueprint $table) {
            $table->dropColumn('is_default');
            $table->dropColumn('challenge_type_slug');
        });
    }
}
