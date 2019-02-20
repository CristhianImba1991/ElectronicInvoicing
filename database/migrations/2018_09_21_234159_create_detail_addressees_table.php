<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDetailAddresseesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('detail_addressees', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('addressee_id');
            $table->unsignedInteger('product_id');
            $table->unsignedDecimal('quantity', 18, 6);
            $table->timestamps();
            $table->foreign('addressee_id')->references('id')->on('addressees');
            $table->foreign('product_id')->references('id')->on('products');
            $table->unique(['addressee_id', 'product_id'], 'detail_addressee_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('detail_addressees');
    }
}
