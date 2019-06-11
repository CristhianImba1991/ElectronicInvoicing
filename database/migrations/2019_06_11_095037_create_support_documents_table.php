<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSupportDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('support_documents', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('ats_retention_id');
            $table->unsignedInteger('support_voucher_id');
            $table->unsignedInteger('voucher_type_id');
            $table->string('support_doc_code', 15);
            $table->date('support_doc_issue_date');
            $table->date('accounting_record_date')->nullable();
            $table->string('support_doc_authorization_number', 49)->nullable();
            $table->unsignedInteger('payment_type_id');
            $table->unsignedInteger('foreign_fiscal_regime_type_id')->nullable();
            $table->unsignedInteger('country_id')->nullable();
            $table->boolean('double_taxation_agreement')->nullable();
            $table->boolean('payment_abroad_subject_retention')->nullable();
            $table->boolean('payment_tax_regime')->nullable();
            $table->timestamps();
            $table->foreign('ats_retention_id')->references('id')->on('ats_retentions');
            $table->foreign('support_voucher_id')->references('id')->on('support_vouchers');
            $table->foreign('voucher_type_id')->references('id')->on('voucher_types');
            $table->foreign('payment_type_id')->references('id')->on('payment_types');
            $table->foreign('foreign_fiscal_regime_type_id')->references('id')->on('foreign_fiscal_regime_types');
            $table->foreign('country_id')->references('id')->on('countries');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('support_documents');
    }
}
