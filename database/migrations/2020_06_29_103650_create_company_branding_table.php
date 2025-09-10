<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompanyBrandingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company_branding', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('company_id')->nullable()->comment("refers to companies table");
            $table->string('onboarding_title', 350)->comment('company login page title');
            $table->text('onboarding_description')->comment('company login page description');
            $table->string('sub_domain', 80)->unique()->comment('domain name of company branding');
            $table->boolean('status')->default(1)->comment('Branding is enable / disable');
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('CASCADE');
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
        Schema::dropIfExists('company_branding');
        Schema::enableForeignKeyConstraints();
    }
}
