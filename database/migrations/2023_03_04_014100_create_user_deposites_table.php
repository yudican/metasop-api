<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_deposites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('payment_code');
            $table->integer('deposit_amount')->default(0);
            $table->boolean('status')->default(0)->comment('0 = Waiting Payment, 1 = Success, 2 = Canceled, 3 = Failed, 4 = Expired');
            $table->string('trx_id')->nullable()->comment('Trx id');
            $table->string('ref')->nullable()->comment('Signature from payment gateway');
            $table->string('paymentUrl')->nullable()->comment('Payment URL from payment gateway');
            $table->string('payment_name')->nullable()->comment('Payment name from payment gateway');
            $table->integer('payment_fee')->default(0)->comment('Payment fee from payment gateway');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_deposites');
    }
};
