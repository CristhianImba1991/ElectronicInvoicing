<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductTaxesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_taxes', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('product_id')->unique();
            $table->unsignedInteger('iva_tax_id')->nullable();
            $table->unsignedInteger('ice_tax_id')->nullable();
            $table->unsignedInteger('irbpnr_tax_id')->nullable();
            $table->timestamps();
            $table->foreign('product_id')->references('id')->on('products');
            $table->foreign('iva_tax_id')->references('id')->on('iva_taxes');
            $table->foreign('ice_tax_id')->references('id')->on('ice_taxes');
            $table->foreign('irbpnr_tax_id')->references('id')->on('irbpnr_taxes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_taxes');
    }
}
