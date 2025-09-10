<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddedCategoryIdInContentPointCalculationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('content_point_calculation', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id')->after('category')->nullable()->comment("refers to All content table based on category.");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('content_point_calculation', function (Blueprint $table) {
            $table->dropColumn('category_id');
        });
    }
}
