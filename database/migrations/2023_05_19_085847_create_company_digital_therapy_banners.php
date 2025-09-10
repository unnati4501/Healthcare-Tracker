<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompanyDigitalTherapyBanners extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company_digital_therapy_banners', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->nullable()->comment("Refers to companies table");
            $table->longText('description')->comment("Banner Us Description");
            $table->integer('order_priority')->default(0)->comment("default 0, flag for order priority");
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
        Schema::dropIfExists('company_digital_therapy_banners');
    }
}
