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
        Schema::create('pharmacybanned', function (Blueprint $table) {
            $table->bigIncrements('PharmacyBannedID');
            $table->integer('PharmacyID')->nullable();
            $table->integer('TypeID')->nullable();
            $table->integer('Type')->nullable()->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pharmacybanned');
    }
};
