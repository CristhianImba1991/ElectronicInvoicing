<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('details', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('voucher_id');
            $table->unsignedInteger('product_id');
            $table->unsignedDecimal('quantity', 18, 6);
            $table->unsignedDecimal('unit_price', 18, 6);
            $table->unsignedDecimal('discount', 18, 6);
            $table->timestamps();
            $table->foreign('voucher_id')->references('id')->on('vouchers');
            $table->foreign('product_id')->references('id')->on('products');
            $table->unique(['voucher_id', 'product_id'], 'detail_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('details');
    }
}
