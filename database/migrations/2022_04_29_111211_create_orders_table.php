<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id');
            $table->string('order_id');
            $table->bigInteger('carrier_id');
            $table->bigInteger('cart_id');
            $table->bigInteger('shipping_address_id');
            $table->bigInteger('billing_address_id');
            $table->bigInteger('status_id');
            $table->string('shipping_address');
            $table->string('billing_address');
            $table->mediumText('comment')->nullable();
            $table->string('shipping_number')->nullable();
            $table->string('invoice_number')->nullable();
            $table->dateTime('invoice_date')->nullable();
            $table->dateTime('shipping_date')->nullable();
            $table->tinyInteger('shipping_type')->nullable()->default(1);
            $table->tinyInteger('payment_method');
            $table->decimal('shipping_price');
            $table->decimal('subtotal');
            $table->decimal('total');
            $table->string('coupon_code');
            $table->tinyInteger('is_partial')->default(0);
            $table->tinyInteger('is_preauth')->default(0);
            $table->tinyInteger('is_paid')->default(0);
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
        Schema::dropIfExists('orders');
    }
}
