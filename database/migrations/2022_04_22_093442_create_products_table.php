<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('brand_id');
            $table->bigInteger('type_id');
            $table->text('name');
            $table->text('description')->nullable();
            $table->text('short_description')->nullable();
            $table->text('notes')->nullable();
            $table->text('sku');
            $table->decimal('delivery_price')->nullable();
            $table->decimal('delivery_tax')->nullable();
            $table->tinyInteger('is_free_shipping')->default(0);
            $table->tinyInteger('is_campaign')->default(0);
            $table->tinyInteger('view_all_images')->default(0);
            $table->bigInteger('featured_variation')->default(0);
            $table->integer('is_new')->default(0);
            $table->integer('is_discounted')->default(0);
            $table->integer('is_featured')->default(0);
            $table->bigInteger('order')->default(0);
            $table->tinyInteger('cimri')->default(0);
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
        Schema::dropIfExists('products');
    }
}
