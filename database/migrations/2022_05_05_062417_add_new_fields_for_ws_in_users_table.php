<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewFieldsForWsInUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedSmallInteger('language')->default(1)->after('avg_steps')->comment("Language for WS 1=>'English',2=>'Polish',3=>'Portuguese',4=>'Spanish',5=>'French',6=>'German',7=>'Italian'");
            $table->unsignedSmallInteger('conferencing_mode')->default(1)->after('language')->comment("Video Conferencing mode for WS 1=>WhereBy, 2=>Custom");
            $table->string('video_link', 250)->nullable()->after('conferencing_mode')->comment('video link for conferencing mode of ws');
            $table->unsignedSmallInteger('shift')->default(1)->after('video_link')->comment("shift for WS 1=>'Morning',2=>'Evening'");
            $table->boolean('sync_email_with_nylas')->default(false)->after('shift')->comment('sync email with nylas for ws');
            $table->text('years_of_experience')->nullable()->after('sync_email_with_nylas')->comment("years of experience is for ws");
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
            //
        });
        Schema::disableForeignKeyConstraints();
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('language');
            $table->dropColumn('conferencing_mode');
            $table->dropColumn('video_link');
            $table->dropColumn('shift');
            $table->dropColumn('sync_email_with_nylas');
            $table->dropColumn('years_of_experience');
        });
        Schema::enableForeignKeyConstraints();
    }
}
