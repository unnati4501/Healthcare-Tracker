<?php

use App\Http\Traits\DisableForeignKeys;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TruncateOldMoodsData extends Migration
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

        Schema::table('moods', function (Blueprint $table) {
            DB::statement("TRUNCATE TABLE mood_user");
            DB::statement("TRUNCATE TABLE moods");
            $table->dropColumn('image');
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
        Schema::table('moods', function (Blueprint $table) {
            $table->string('image', 255)->nullable()->comment('mood image');
        });
    }
}
