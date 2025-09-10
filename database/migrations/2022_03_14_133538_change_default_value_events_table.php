<?php

use App\Http\Traits\DisableForeignKeys;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeDefaultValueEventsTable extends Migration
{
    use DisableForeignKeys;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropForeign('events_subcategory_id_foreign');
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
        Schema::table('events', function (Blueprint $table) {
            $table->foreign('subcategory_id')
                ->references('id')->on('sub_categories')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
        Schema::enableForeignKeyConstraints();
    }
}
