<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateImportZipCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('import_zip_codes', function (Blueprint $table) {
            $table->id();
            $table->text('il')->nullable();
            $table->text('ilce')->nullable();
            $table->text('semt')->nullable();
            $table->text('mahalle')->nullable();
            $table->text('pk')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('import_zip_codes');
    }
}
