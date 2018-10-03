<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdditionalDetailAddresseesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('additional_detail_addressees', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('detail_addressee_id');
            $table->string('name', 30);
            $table->string('value', 300);
            $table->timestamps();
            $table->foreign('detail_addressee_id')->references('id')->on('detail_addressees');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('additional_detail_addressees');
    }
}
