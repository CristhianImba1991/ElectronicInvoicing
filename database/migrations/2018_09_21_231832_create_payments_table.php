<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
            $table->increments('id');
            $table->unsignedInteger('voucher_id');
            $table->unsignedInteger('payment_method_id');
            $table->unsignedInteger('time_unit_id');
            $table->unsignedDecimal('total', 18, 6);
            $table->unsignedInteger('term');
            $table->timestamps();
            $table->foreign('voucher_id')->references('id')->on('vouchers');
            $table->foreign('payment_method_id')->references('id')->on('payment_methods');
            $table->foreign('time_unit_id')->references('id')->on('time_units');
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
