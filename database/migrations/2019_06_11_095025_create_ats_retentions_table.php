<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAtsRetentionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ats_retentions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('voucher_id')->unique();
            $table->unsignedInteger('supplier_identification_type_id');
            $table->boolean('related_party')->default(1);
            $table->timestamps();
            $table->foreign('voucher_id')->references('id')->on('vouchers');
            $table->foreign('supplier_identification_type_id')->references('id')->on('supplier_identification_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ats_retentions');
    }
}
