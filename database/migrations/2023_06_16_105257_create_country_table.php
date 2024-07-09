<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('country', function (Blueprint $table) {
            $table->bigIncrements('CountryID');
            $table->string('Name', 80)->nullable();
            $table->integer('RegionID')->nullable();
            $table->integer('Status')->nullable();
            $table->string('CodeName2', 2)->nullable();
            $table->string('CodeName3', 3)->nullable();
            $table->float('Digital', 12)->nullable();
            $table->float('Physical', 12)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('country');
    }
};
