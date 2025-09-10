<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateStatusFieldZcQuestionsTable extends Migration
{
    /**
     * Custructor
     *
     * @return Null
     */
    public function __construct()
    {
        DB::connection()->getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('zc_questions', function (Blueprint $table) {
            DB::statement("ALTER TABLE `zc_questions` MODIFY COLUMN `status` ENUM('0','1','2')  NOT NULL DEFAULT '0' COMMENT ' Question status updated. 0 = Draft, 1 = publish, 2 = Review'");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('zc_questions', function (Blueprint $table) {
            $table->boolean('status')->default(1)->change();
        });
    }
}
