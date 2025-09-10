<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAvailabilityStatusColumToUsersTable extends Migration
{
    public function __construct()
    {
        DB::getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('availability_status')->default(0)->comment('1 -> available;2 -> custom leave; 0 -> unavailable;')->after('is_coach');
            $table->string('coach_timezone', 255)->nullable()->comment('Coach availability timezone')->after('is_coach');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('availability_status');
            $table->dropColumn('coach_timezone');
        });
    }
}
