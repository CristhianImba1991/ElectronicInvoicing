<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIceTaxesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ice_taxes', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('code');
            $table->unsignedInteger('auxiliary_code');
            $table->string('description', 300);
            $table->unsignedDecimal('specific_rate', 5, 2)->nullable();
            $table->unsignedDecimal('ad_valorem_rate', 5, 2)->nullable();
            $table->timestamps();
            $table->unique(['code', 'auxiliary_code'], 'ice_tax_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ice_taxes');
    }
}
