<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateDescriptionColumnInFeedTable extends Migration
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
        Schema::table('feeds', function (Blueprint $table) {
            $table->longText('description')->comment('feed description')->nullable()->change();
            $table->enum('type', ['1', '2', '3', '4'])->comment('1 => Audio, 2 => Video, 3 => Youtube Link, 4 => Content')->nullable()->after('sub_category_id');
            $table->boolean('is_stick')->default(0)->comment('To identify feed is stick or not')->after('description');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('feeds', function (Blueprint $table) {
            $table->text('description')->comment('feed description')->change();
            if (Schema::hasColumn('feeds', 'type')) {
                $table->dropColumn('type');
            }
            if (Schema::hasColumn('feeds', 'is_stick')) {
                $table->dropColumn('is_stick');
            }
        });
    }
}
