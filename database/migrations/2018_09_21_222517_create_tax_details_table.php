<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTaxDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tax_details', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('detail_id');
            $table->unsignedSmallInteger('code');
            $table->unsignedSmallInteger('percentage_code');
            $table->unsignedDecimal('rate', 5, 2);
            $table->unsignedDecimal('tax_base', 18, 6);
            $table->unsignedDecimal('value', 18, 6);
            $table->timestamps();
            $table->foreign('detail_id')->references('id')->on('details');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tax_details');
    }
}
