<?php

use App\Http\Traits\DisableForeignKeys;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropCompanyIdForeignChallengeWiseCompanyPointsTable extends Migration
{
    use DisableForeignKeys;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Disable foreign key checks!
        $this->disableForeignKeys();

        Schema::table('challenge_wise_company_points', function (Blueprint $table) {
            $table->dropForeign('challenge_wise_company_points_company_id_foreign');
        });

        // Enable foreign key checks!
        $this->enableForeignKeys();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Disable foreign key checks!
        $this->disableForeignKeys();

        Schema::table('challenge_wise_company_points', function (Blueprint $table) {
            $table->foreign('company_id')
                ->references('id')->on('companies')
                ->onDelete('cascade');
        });

        // Enable foreign key checks!
        $this->enableForeignKeys();
    }
}
