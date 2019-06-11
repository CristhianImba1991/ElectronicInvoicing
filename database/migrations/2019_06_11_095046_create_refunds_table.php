<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRefundsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('refunds', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('support_document_id');
            $table->unsignedInteger('identification_type_id');
            $table->string('identification', 20);
            $table->unsignedInteger('country_id');
            $table->unsignedInteger('supplier_identification_type_id');
            $table->string('support_doc_code', 49);
            $table->timestamps();
            $table->foreign('support_document_id')->references('id')->on('support_documents');
            $table->foreign('identification_type_id')->references('id')->on('identification_types');
            $table->foreign('country_id')->references('id')->on('countries');
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
        Schema::dropIfExists('refunds');
    }
}
