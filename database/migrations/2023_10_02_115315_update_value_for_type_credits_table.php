<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateValueForTypeCreditsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('company_wise_credits', function (Blueprint $table) {
            $table->dropColumn('type');
        });

        Schema::table('company_wise_credits', function (Blueprint $table) {
            $table->enum('type', ['Add', 'Remove', 'On Hold'])->default('Add')->comment('Add : add the credit to avaliable credits, Remove : remove the credits from available credits, On hold : hold credits')->after('user_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        
    }
}
