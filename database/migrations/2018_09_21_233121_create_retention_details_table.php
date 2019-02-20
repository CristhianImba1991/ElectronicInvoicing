<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRetentionDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('retention_details', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('retention_id');
            $table->unsignedInteger('retention_tax_description_id');
            $table->unsignedDecimal('tax_base', 18, 6);
            $table->unsignedDecimal('rate', 5, 2);
            $table->string('support_doc_code', 49)->nullable();
            $table->timestamps();
            $table->foreign('retention_id')->references('id')->on('retentions');
            $table->foreign('retention_tax_description_id')->references('id')->on('retention_tax_descriptions');
            $table->unique(['retention_id', 'retention_tax_description_id'], 'retention_details_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('retention_details');
    }
}
