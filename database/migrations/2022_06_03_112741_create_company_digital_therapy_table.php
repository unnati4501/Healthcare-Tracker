<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompanyDigitalTherapyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company_digital_therapy', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->nullable()->comment("refers to companies table");
            $table->boolean('dt_is_online')->default(0)->comment('1 if degital therapy mode online');
            $table->boolean('dt_is_onsite')->default(0)->comment('1 if degital therapy mode onsite');
            $table->string('dt_wellbeing_sp_ids')->nullable()->comment('get ids of wellbeing specialist');
            $table->bigInteger('dt_session_cancellation')->default(0);
            $table->bigInteger('dt_session_reschedule')->default(0);
            $table->bigInteger('dt_advanced_booking')->default(0);
            $table->bigInteger('dt_future_booking')->default(0);
            $table->bigInteger('dt_counselling_duration')->default(0);
            $table->bigInteger('dt_coaching_duration')->default(0);
            $table->bigInteger('dt_max_sessions_user')->default(0);
            $table->bigInteger('dt_max_sessions_company')->default(0);
            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");

            $table->foreign('company_id')
                ->references('id')->on('companies')
                ->onDelete('cascade');
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
        Schema::dropIfExists('company_digital_therapy');
        Schema::enableForeignKeyConstraints();
    }
}
