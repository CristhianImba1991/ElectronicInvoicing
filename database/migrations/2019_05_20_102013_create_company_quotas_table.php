<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompanyQuotasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company_quotas', function (Blueprint $table) {
            $table->unsignedInteger('company_id');
            $table->unsignedInteger('quotas_id');
            $table->foreign('company_id')->references('id')->on('companies');
            $table->foreign('quotas_id')->references('id')->on('quotas');
            $table->unique(['company_id', 'quotas_id'], 'company_quotas_unique');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('company_quotas');
    }
}
