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
            $table->string('internal_code', 25);
            $table->string('additional_code', 25);
            $table->string('description', 300);
            $table->unsignedDecimal('quantity', 18, 6);
            $table->timestamps();
            $table->foreign('addressee_id')->references('id')->on('addressees');
            $table->unique(['addressee_id', 'internal_code', 'additional_code'], 'detail_addressee_unique');
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
