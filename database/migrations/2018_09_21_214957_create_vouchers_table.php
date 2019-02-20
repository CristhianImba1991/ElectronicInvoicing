<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVouchersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('emission_point_id');
            $table->unsignedInteger('voucher_type_id');
            $table->unsignedInteger('environment_id');
            $table->unsignedInteger('voucher_state_id');
            $table->unsignedInteger('sequential');
            $table->unsignedInteger('numeric_code');
            $table->unsignedInteger('customer_id');
            $table->date('issue_date');
            $table->dateTimeTz('authorization_date')->nullable();
            $table->unsignedInteger('currency_id');
            $table->unsignedDecimal('tip', 18, 6)->nullable();
            $table->unsignedDecimal('iva_retention', 18, 6)->nullable();
            $table->unsignedDecimal('rent_retention', 18, 6)->nullable();
            $table->string('xml', 300)->nullable();
            $table->text('extra_detail')->nullable();
            $table->unsignedInteger('user_id');
            $table->string('support_document', 49)->nullable();
            $table->date('support_document_date')->nullable();
            $table->timestamps();
            $table->foreign('emission_point_id')->references('id')->on('emission_points');
            $table->foreign('voucher_type_id')->references('id')->on('voucher_types');
            $table->foreign('environment_id')->references('id')->on('environments');
            $table->foreign('voucher_state_id')->references('id')->on('voucher_states');
            $table->foreign('customer_id')->references('id')->on('customers');
            $table->foreign('currency_id')->references('id')->on('currencies');
            $table->foreign('user_id')->references('id')->on('users');
            $table->unique(['emission_point_id', 'voucher_type_id', 'environment_id', 'voucher_state_id', 'sequential'], 'voucher_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vouchers');
    }
}
