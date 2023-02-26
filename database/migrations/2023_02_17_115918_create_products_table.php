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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('product_original_name')->nullable();
            $table->string('product_name')->nullable();
            $table->string('product_slug')->nullable();
            $table->string('product_description')->nullable();
            $table->integer('product_original_price')->nullable()->default(0);
            $table->integer('product_price')->nullable()->default(0);
            $table->string('product_image')->nullable();
            $table->boolean('product_status')->nullable();
            $table->integer('product_stock')->nullable();
            $table->string('product_category')->nullable();
            $table->string('product_brand')->nullable();
            $table->string('product_sku')->nullable();
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
        Schema::dropIfExists('products');
    }
};
