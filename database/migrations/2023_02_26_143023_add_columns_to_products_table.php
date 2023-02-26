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
        Schema::table('products', function (Blueprint $table) {
            $table->integer('vendor_admin_fee')->after('product_sku')->nullable()->default(0);
            $table->integer('admin_fee')->after('product_sku')->nullable()->default(0);
            $table->integer('commission')->after('admin_fee')->nullable()->default(0);
            $table->enum('product_type', ['prepaid', 'pasca'])->default('prepaid')->after('commission');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('admin_fee');
            $table->dropColumn('commission');
            $table->dropColumn('product_type');
        });
    }
};
