<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFileImportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('file_imports', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of current table");
            
            $table->unsignedBigInteger('company_id')->comment("refers to companies table");

            $table->string('module', 255)->comment("module name of which data needs to be imported");
            $table->string('token', 255)->comment("token to identify the files");
            $table->string('uploaded_file')->comment("uploaded file name requested to import the data");
            $table->string('validated_file')->nullable()->comment("corrected file name requested to import the data");
            $table->boolean('in_process')->default(0)->comment('1 => File is being processed, 0 => File is not being processed');
            $table->boolean('is_processed')->default(0)->comment('1 => File is processed, 0 => File is not yet processed');
            $table->boolean('is_imported_successfully')->default(0)->comment('1 => File imported without any error, 0 => File could not be imported.');
            $table->dateTime('process_started_at')->nullable()->comment("date and time when process is started to import file data");
            $table->dateTime('process_finished_at')->nullable()->comment("date and time when process is completed to import file data");

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
        Schema::dropIfExists('file_imports');
        Schema::enableForeignKeyConstraints();
    }
}
