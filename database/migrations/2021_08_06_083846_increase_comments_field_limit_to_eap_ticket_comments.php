<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class IncreaseCommentsFieldLimitToEapTicketComments extends Migration
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
        Schema::table('eap_ticket_comments', function (Blueprint $table) {
            $table->longText('comment')->change();
            \DB::raw("ALTER TABLE `eap_ticket_comments` CHANGE `comment` `comment` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'comment body';");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('eap_ticket_comments', function (Blueprint $table) {
            \DB::raw("ALTER TABLE `eap_ticket_comments` CHANGE `comment` `comment` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'comment body';");
        });
    }
}
