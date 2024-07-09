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
        Schema::create('pharmacy', function (Blueprint $table) {
            $table->bigIncrements('PharmacyID');
            $table->string('Title', 50)->nullable();
            $table->string('Location', 50)->nullable();
            $table->string('AccountNumber', 100)->nullable();
            $table->string('BillingAccountNumber', 100)->nullable();
            $table->string('ShipperName', 100)->nullable();
            $table->string('VATNumber', 100)->nullable();
            $table->string('EORI', 100)->nullable();
            $table->string('Telephone', 100)->nullable();
            $table->string('Email', 100)->nullable();
            $table->string('Address1', 50)->nullable();
            $table->string('Address2', 50)->nullable();
            $table->string('Address3', 50)->nullable();
            $table->string('Address4', 50)->nullable();
            $table->string('Postcode', 50)->nullable();
            $table->string('Contents', 50)->nullable()->default('Prescription Medicine');
            $table->integer('CountryCode')->nullable();
            $table->integer('Status')->nullable()->default(1);
            $table->timestamp('CreatedAt')->nullable()->useCurrent();
            $table->timestamp('UpdatedAt')->useCurrentOnUpdate()->nullable();
            $table->timestamp('DeletedAt')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pharmacy');
    }
};
