<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSupportDocumentRetentionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('support_document_retentions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('support_document_id');
            $table->unsignedInteger('retention_tax_description_id');
            $table->unsignedDecimal('tax_base', 18, 6);
            $table->unsignedDecimal('rate', 5, 2);
            $table->date('dividend_payment_date')->nullable();
            $table->unsignedDecimal('income_tax', 18, 6)->nullable();
            $table->date('profits_attributable_dividend_fiscal_period')->nullable();
            $table->unsignedInteger('quantity_banana_boxes')->nullable();
            $table->unsignedDecimal('price_banana_box')->nullable();
            $table->timestamps();
            $table->foreign('support_document_id')->references('id')->on('support_documents');
            $table->foreign('retention_tax_description_id')->references('id')->on('retention_tax_descriptions');
            $table->unique(['support_document_id', 'retention_tax_description_id'], 'support_document_retention_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('support_document_retentions');
    }
}
