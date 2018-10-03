<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->increments('id');
            $table->string('ruc', 13)->unique();
            $table->string('social_reason', 300);
            $table->string('tradename', 300);
            $table->string('address', 300);
            $table->string('special_contributor', 13);
            $table->boolean('keep_accounting');
            $table->string('phone', 30);
            $table->string('logo', 300);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('companies');
    }
}
