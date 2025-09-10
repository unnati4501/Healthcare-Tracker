<?php

use App\Http\Traits\DisableForeignKeys;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeCompanyIdNullableFileImportsTable extends Migration
{
    use DisableForeignKeys;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->disableForeignKeys();

        Schema::table('file_imports', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->nullable()->change()->comment("refers to companies table");
        });

        $this->enableForeignKeys();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->disableForeignKeys();

        Schema::table('file_imports', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->change()->comment("refers to companies table");
        });

        $this->enableForeignKeys();
    }
}
