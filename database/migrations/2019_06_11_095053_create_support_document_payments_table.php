<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSupportDocumentPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('support_document_payments', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('support_document_id');
            $table->unsignedInteger('payment_method_id');
            $table->unsignedDecimal('total', 18, 6);
            $table->timestamps();
            $table->foreign('support_document_id')->references('id')->on('support_documents');
            $table->foreign('payment_method_id')->references('id')->on('payment_methods');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('support_document_payments');
    }
}
