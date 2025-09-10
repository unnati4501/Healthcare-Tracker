<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompanyBrandingContactDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company_branding_contact_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->nullable()->comment("refers to companies table");
            $table->string('contact_us_header', 100)->comment("Contact Us Header");
            $table->longText('contact_us_description')->comment("Contact Us Description");
            $table->string('contact_us_request', 100)->comment("clinicl support => Zevo Health Zendesk Account, technical support => Zevo Therapy Zendesk Account");
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
        Schema::dropIfExists('company_branding_contact_details');
        Schema::enableForeignKeyConstraints();
    }
}
