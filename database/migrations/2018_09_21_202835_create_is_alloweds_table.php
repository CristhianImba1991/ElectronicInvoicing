<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIsAllowedsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('is_alloweds', function (Blueprint $table) {
            $table->unsignedInteger('emission_point_id');
            $table->unsignedInteger('user_id');
            $table->timestamps();
            $table->foreign('emission_point_id')->references('id')->on('emission_points');
            $table->foreign('user_id')->references('id')->on('users');
            $table->unique(['emission_point_id', 'user_id'], 'is_allowed_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('is_alloweds');
    }
}
