<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDebitNotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('debit_notes', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('debit_note_tax_id');
            $table->string('reason', 300);
            $table->unsignedDecimal('value', 18, 6);
            $table->timestamps();
            $table->foreign('debit_note_tax_id')->references('id')->on('debit_note_taxes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('debit_notes');
    }
}
