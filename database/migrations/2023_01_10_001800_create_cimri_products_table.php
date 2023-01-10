<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCimriProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cimri_products', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('merchantItemId');
            $table->bigInteger('merchantItemCategoryId');
            $table->text('merchantItemCategoryName')->nullable();
            $table->text('brand')->nullable();
            $table->text('itemTitle')->nullable();
            $table->text('itemUrl')->nullable();
            $table->text('itemImageUrl')->nullable();
            $table->decimal('price3T')->default(0);
            $table->decimal('price6T')->default(0);
            $table->decimal('priceEft')->default(0);
            $table->decimal('pricePlusTax')->default(0);
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
        Schema::dropIfExists('cimri_products');
    }
}
