<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateDateColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('company_branding', function (Blueprint $table) {
            if (Schema::hasColumn('company_branding', 'created_at')) {
                $table->dropColumn('created_at');
            }
            if (Schema::hasColumn('company_branding', 'updated_at')) {
                $table->dropColumn('updated_at');
            }
        });

        Schema::table('company_branding', function (Blueprint $table) {
            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");
        });
    }
}
