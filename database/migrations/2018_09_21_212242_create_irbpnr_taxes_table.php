<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIrbpnrTaxesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('irbpnr_taxes', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('code');
            $table->unsignedInteger('auxiliary_code');
            $table->string('description', 50);
            $table->unsignedDecimal('specific_rate', 5, 2)->nullable();
            $table->timestamps();
            $table->unique(['code', 'auxiliary_code'], 'irbpnr_tax_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('irbpnr_taxes');
    }
}
