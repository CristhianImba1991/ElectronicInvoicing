<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuotasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quotas', function (Blueprint $table) {
            $table->increments('id');
            $table->string('description', 30);
            $table->unsignedSmallInteger('max_users_owner')->nullable();
            $table->unsignedSmallInteger('max_users_supervisor')->nullable();
            $table->unsignedSmallInteger('max_users_employee')->nullable();
            $table->unsignedSmallInteger('max_branches')->nullable();
            $table->unsignedSmallInteger('max_emission_points')->nullable();
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
        Schema::dropIfExists('quotas');
    }
}
