<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIncreasingDesisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('increasing_desis', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('carrier_id');
            $table->decimal('cat_1_price')->default(0);
            $table->decimal('cat_2_price')->default(0);
            $table->decimal('cat_3_price')->default(0);
            $table->tinyInteger('active')->default(1);
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
        Schema::dropIfExists('increasing_desis');
    }
}
