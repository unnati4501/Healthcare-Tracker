<?php

use App\Http\Traits\DisableForeignKeys;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateCourseLessionTable extends Migration
{
    use DisableForeignKeys;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Disable foreign key checks!
        $this->disableForeignKeys();

        Schema::table('course_lessions', function (Blueprint $table) {
            $table->dropForeign('course_lessions_course_week_id_foreign');
        });

        Schema::table('course_lessions', function (Blueprint $table) {
            $table->unsignedBigInteger('course_week_id')->nullable()->change()->comment("refers to course_weeks table");
            $table->longText('description')->change()->comment('lession description');
            $table->boolean('auto_progress')->default(false)->after('is_default')->comment("default false, flag sets to true when lession has auto progress");
            $table->integer('order_priority')->default(0)->after('auto_progress')->comment("default 0, flag for order priority");
            $table->enum('type', ['audio', 'video', 'youtube', 'content'])->after('order_priority');
        });

        // Enable foreign key checks!
        $this->enableForeignKeys();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Disable foreign key checks!
        $this->disableForeignKeys();

        Schema::table('course_lessions', function (Blueprint $table) {
            $table->dropColumn('auto_progress');
            $table->dropColumn('order_priority');
            $table->dropColumn('type');
        });

        Schema::table('course_lessions', function (Blueprint $table) {
            $table->unsignedBigInteger('course_week_id')->change()->comment("refers to course_weeks table");
            $table->longText('description')->change()->comment('lession description');
            $table->foreign('course_week_id')
                ->references('id')
                ->on('course_weeks')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');
        });

        // Enable foreign key checks!
        $this->enableForeignKeys();
    }
}
