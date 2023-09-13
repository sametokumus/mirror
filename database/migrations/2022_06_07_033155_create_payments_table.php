<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('order_id');
            $table->string('payment_id');
            $table->decimal('default_price');
            $table->decimal('paid_price');
            $table->bigInteger('type');
            $table->bigInteger('bank_id')->nullable();
            $table->bigInteger('installment');
            $table->string('return_code')->nullable();
            $table->string('response')->nullable();
            $table->string('transaction_id')->nullable();
            $table->string('transaction_date')->nullable();
            $table->string('hostrefnum')->nullable();
            $table->string('authcode')->nullable();
            $table->tinyInteger('is_preauth')->default(0);
            $table->tinyInteger('is_paid')->default(0);
            $table->tinyInteger('is_cancel_preauth')->default(0);
            $table->tinyInteger('is_refund')->default(0);
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
        Schema::dropIfExists('payments');
    }
}
