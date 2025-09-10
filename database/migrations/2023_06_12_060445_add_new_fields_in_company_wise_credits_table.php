<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewFieldsInCompanyWiseCreditsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('company_wise_credits', function (Blueprint $table) {
            $table->enum('type', ['Add', 'Remove'])->default('Add')->after('user_name')->comment('Add : add the credit to avaliable credits, Remove : remove the credits from available credits');
            $table->integer('available_credits')->default(0)->after('credits')->comment('will calculate total available credits after add or remove');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('company_wise_credits', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->dropColumn('available_credits');
        });
    }
}
