<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAddresseesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('addressees', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('waybill_id');
            $table->string('identification', 20);
            $table->string('social_reason', 300);
            $table->string('address', 300);
            $table->string('transfer_reason', 300);
            $table->string('single_customs_doc', 20);
            $table->unsignedSmallInteger('destination_establishment_code');
            $table->string('route', 300);
            $table->string('support_doc_code', 40);
            $table->timestamps();
            $table->foreign('waybill_id')->references('id')->on('waybills');
            $table->unique(['waybill_id', 'identification'], 'addressee_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('addressees');
    }
}
