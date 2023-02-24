<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductRulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_rules', function (Blueprint $table) {
            $table->id();
            $table->text('micro_name')->nullable();
            $table->text('micro_sku')->nullable();
            $table->text('dimensions')->nullable();
            $table->text('weight')->nullable();
            $table->bigInteger('package_type_id')->nullable();
            $table->bigInteger('variation_id')->nullable();
            $table->bigInteger('quantity_stock')->nullable();
            $table->bigInteger('quantity_min')->nullable();
            $table->bigInteger('quantity_step')->nullable();
            $table->tinyInteger('is_free_shipping')->nullable();
            $table->decimal('discount_rate')->nullable();
            $table->decimal('tax_rate')->nullable();
            $table->decimal('regular_price')->nullable();
            $table->decimal('regular_tax')->nullable();
            $table->decimal('discounted_price')->nullable();
            $table->decimal('discounted_tax')->nullable();
            $table->text('currency')->nullable();
            $table->tinyInteger('material')->default(0);
            $table->tinyInteger('status')->default(1);
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
        Schema::dropIfExists('product_rules');
    }
}
