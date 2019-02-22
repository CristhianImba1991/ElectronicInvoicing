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
            $table->unsignedInteger('customer_id');
            $table->string('address', 300);
            $table->string('transfer_reason', 300);
            $table->string('single_customs_doc', 20)->nullable();
            $table->unsignedSmallInteger('destination_establishment_code')->nullable();
            $table->string('route', 300);
            $table->string('support_doc_code', 49);
            $table->timestamps();
            $table->foreign('waybill_id')->references('id')->on('waybills');
            $table->foreign('customer_id')->references('id')->on('customers');
            $table->unique(['waybill_id', 'customer_id'], 'addressee_unique');
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
