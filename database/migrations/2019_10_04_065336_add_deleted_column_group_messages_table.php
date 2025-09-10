<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDeletedColumnGroupMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('group_messages', function (Blueprint $table) {
            $table->boolean('deleted')->default(false)->after('model_name')->comment('true, if user has deleted the group message');
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
                if (Schema::hasColumn('group_messages', 'deleted')) {
                    $table->dropColumn('deleted');
                }
            });
        }
        Schema::enableForeignKeyConstraints();
    }
}
