<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRetentionTaxDescriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('retention_tax_descriptions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('retention_tax_id');
            $table->string('code', 5);
            $table->string('description', 300);
            $table->timestamps();
            $table->foreign('retention_tax_id')->references('id')->on('retention_taxes');
            $table->unique(['retention_tax_id', 'code'], 'retention_tax_description_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('retention_tax_descriptions');
    }
}
