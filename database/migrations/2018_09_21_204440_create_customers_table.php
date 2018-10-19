<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('identification_type_id');
            $table->string('identification', 20);
            $table->string('social_reason', 300);
            $table->string('address', 300);
            $table->string('phone', 30);
            $table->string('email', 300);
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('identification_type_id')->references('id')->on('identification_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customers');
    }
}
