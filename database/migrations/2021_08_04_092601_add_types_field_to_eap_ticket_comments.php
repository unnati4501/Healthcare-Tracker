<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypesFieldToEapTicketComments extends Migration
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
            $table->enum('type', ['internal_note', 'counsellor'])->default('internal_note')->after('comment_id')->comment('type of comment');
            $table->unsignedBigInteger('user_id')->nullable()->comment("refers to the users table")->after('ticket_id');
            $table->unsignedBigInteger('comment_id')->nullable()->change();
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('CASCADE');
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
        Schema::table('eap_ticket_comments', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->dropForeign('eap_ticket_comments_user_id_foreign');
            $table->dropColumn('user_id');
        });
        Schema::enableForeignKeyConstraints();
    }
}
