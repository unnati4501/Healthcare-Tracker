<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForwardedColumnInGroupMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('group_messages', function (Blueprint $table) {
            $table->boolean('forwarded')->default(false)->after('model_name')->comment('true, if user has forwarded the group message');
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
        if (Schema::hasTable('group_messages')) {
            Schema::table('group_messages', function (Blueprint $table) {
                if (Schema::hasColumn('group_messages', 'forwarded')) {
                    $table->dropColumn('forwarded');
                }
            });
        }
        Schema::enableForeignKeyConstraints();
    }
}
