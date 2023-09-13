<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBankRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bank_requests', function (Blueprint $table) {
            $table->id();
            $table->string('payment_id');
            $table->text('pos_request')->nullable();
            $table->text('pos_response')->nullable();
            $table->integer('type')->nullable();
            $table->tinyInteger('success')->default(0);
            $table->text('transaction_id')->nullable();
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
        Schema::dropIfExists('bank_requests');
    }
}
