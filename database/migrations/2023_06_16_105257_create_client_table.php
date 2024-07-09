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
        Schema::create('client', function (Blueprint $table) {
            $table->bigIncrements('ClientID');
            $table->string('CompanyName', 100)->nullable();
            $table->string('Title', 50)->nullable();
            $table->string('Name', 100)->nullable();
            $table->string('Middlename', 100)->nullable();
            $table->string('Surname', 100)->nullable();
            $table->string('Address1', 50)->nullable();
            $table->string('Address2', 50)->nullable();
            $table->string('Address3', 50)->nullable();
            $table->string('Address4', 50)->nullable();
            $table->string('Postcode', 50)->nullable();
            $table->integer('CountryID')->nullable();
            $table->string('Telephone', 50)->nullable();
            $table->string('Mobile', 50)->nullable();
            $table->string('Email', 350)->nullable();
            $table->float('CreditLimit', 6)->nullable();
            $table->integer('CreatedDate')->nullable();
            $table->integer('ModifiedDate')->nullable();
            $table->integer('AccessedDate')->nullable();
            $table->text('IP')->nullable();
            $table->integer('Type')->nullable();
            $table->integer('Status')->nullable();
            $table->text('Notes')->nullable();
            $table->string('CompanyNumber', 20)->nullable();
            $table->string('GPHCNO', 20)->nullable();
            $table->string('ReturnURL', 200)->nullable();
            $table->string('Username', 50)->nullable();
            $table->string('Password', 50)->nullable();
            $table->string('APIKey', 50)->nullable();
            $table->string('ITName', 100)->nullable();
            $table->string('ITEmail', 100)->nullable();
            $table->string('TradingName', 100)->nullable();
            $table->text('AdditionalComment')->nullable();
            $table->string('ReturnUsername', 50)->nullable();
            $table->string('ReturnPassword', 50)->nullable();
            $table->float('VAT', 6)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('client');
    }
};
