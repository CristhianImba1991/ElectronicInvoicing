<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRefundTaxesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('refund_taxes', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('refund_id');
            $table->unsignedSmallInteger('code');
            $table->unsignedSmallInteger('percentage_code');
            $table->unsignedDecimal('rate', 5, 2);
            $table->unsignedDecimal('tax_base', 18, 6);
            $table->unsignedDecimal('value', 18, 6);
            $table->timestamps();
            $table->foreign('refund_id')->references('id')->on('refunds');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('refund_taxes');
    }
}
