<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCreditNoteTaxesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('credit_note_taxes', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('credit_note_id');
            $table->unsignedSmallInteger('code');
            $table->unsignedSmallInteger('percentage_code');
            $table->unsignedDecimal('rate', 5, 2);
            $table->unsignedDecimal('tax_base', 18, 6);
            $table->unsignedDecimal('value', 18, 6);
            $table->timestamps();
            $table->foreign('credit_note_id')->references(['id'])->on('credit_notes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('credit_note_taxes');
    }
}
