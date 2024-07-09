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
        Schema::create('productcode', function (Blueprint $table) {
            $table->bigIncrements('ProductCodeID');
            $table->string('Code', 100)->nullable();
            $table->string('FDBID', 48)->default('0')->index('FDBID');
            $table->string('Name', 200)->nullable();
            $table->integer('Type')->nullable();
            $table->integer('Status')->nullable();
            $table->float('Quantity', 10, 0)->nullable();
            $table->string('Units', 20)->nullable();
            $table->integer('Fridge')->nullable();
            $table->float('VAT', 12)->nullable();
            $table->integer('Pack')->nullable();
            $table->integer('OTC')->nullable();
            $table->integer('ProductType')->nullable();
            $table->integer('JVM')->nullable()->default(2)->comment('Is the product pouch dispensable');
            $table->integer('TariffCode')->nullable();
            $table->integer('PrintForm')->nullable()->default(0);

            $table->index(['Status', 'Code'], 'StatusCode');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('productcode');
    }
};
