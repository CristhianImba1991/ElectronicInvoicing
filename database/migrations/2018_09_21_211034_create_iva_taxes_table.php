<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIvaTaxesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('iva_taxes', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('code');
            $table->unsignedInteger('auxiliary_code');
            $table->string('description', 50);
            $table->unsignedDecimal('rate', 5, 2)->nullable();
            $table->timestamps();
            $table->unique(['code', 'auxiliary_code'], 'iva_tax_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('iva_taxes');
    }
}
