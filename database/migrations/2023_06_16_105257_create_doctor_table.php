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
        Schema::create('doctor', function (Blueprint $table) {
            $table->bigIncrements('DoctorID');
            $table->string('Title', 50)->nullable();
            $table->string('CompanyName', 50)->nullable();
            $table->string('Name', 100)->nullable();
            $table->string('Surname', 100)->nullable();
            $table->string('Address1', 50)->nullable();
            $table->string('Address2', 50)->nullable();
            $table->string('Address3', 50)->nullable();
            $table->string('Address4', 50)->nullable();
            $table->string('Postcode', 50)->nullable();
            $table->integer('CountryID')->nullable();
            $table->string('Telephone', 50)->nullable();
            $table->string('Mobile', 50)->nullable();
            $table->string('Email', 100)->nullable();
            $table->integer('CreatedDate')->nullable();
            $table->integer('ModifiedDate')->nullable();
            $table->integer('AccessedDate')->nullable();
            $table->integer('Status')->nullable();
            $table->text('Notes')->nullable();
            $table->string('GMCNO', 20)->nullable();
            $table->string('MedicalInsuranceNo', 20)->nullable();
            $table->string('Password', 50)->nullable();
            $table->string('Username', 100)->nullable();
            $table->integer('DoctorType')->nullable();
            $table->integer('ParentID')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('doctor');
    }
};
