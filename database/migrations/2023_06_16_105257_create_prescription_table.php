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
        Schema::create('prescription', function (Blueprint $table) {
            $table->bigIncrements('PrescriptionID');
            $table->integer('PharmacyID')->nullable();
            $table->integer('DoctorID')->nullable();
            $table->string('GMCNO', 20)->nullable();
            $table->string('DoctorName', 200)->nullable();
            $table->integer('ClientID')->nullable()->index('ClientID');
            $table->string('ReferenceNumber', 50)->nullable()->index('ReferenceNumber');
            $table->string('Email', 100)->nullable();
            $table->string('GUID', 50)->nullable();
            $table->string('TokenID', 50)->nullable();
            $table->string('Title', 50)->nullable();
            $table->string('Name', 100)->nullable()->index('Name');
            $table->string('Middlename', 100)->nullable();
            $table->string('Surname', 100)->nullable()->index('Surname');
            $table->string('DOB', 10)->nullable()->index('DOB');
            $table->string('Sex', 1)->nullable();
            $table->float('BMI', 10, 0)->nullable();
            $table->string('Address1', 50)->nullable();
            $table->string('Address2', 50)->nullable();
            $table->string('Address3', 50)->nullable();
            $table->string('Address4', 50)->nullable();
            $table->string('Postcode', 50)->nullable();
            $table->integer('CountryCode')->nullable();
            $table->string('DAddress1', 50)->nullable();
            $table->string('DAddress2', 50)->nullable();
            $table->string('DAddress3', 50)->nullable();
            $table->string('DAddress4', 50)->nullable();
            $table->string('DPostcode', 50)->nullable()->index('DPostcode');
            $table->integer('DCountryCode')->nullable()->index('DCountryCode');
            $table->string('Telephone', 50)->nullable();
            $table->string('Mobile', 50)->nullable();
            $table->integer('PaymentMethod')->nullable();
            $table->integer('Exemption')->nullable()->index('Exemption');
            $table->integer('CreatedDate')->nullable()->index('create_date');
            $table->text('Notes')->nullable();
            $table->string('Repeats', 10)->nullable();
            $table->integer('Status')->nullable()->index('status');
            $table->integer('SubStatus')->nullable()->index('SubStatus');
            $table->integer('JVM')->nullable()->default(0)->index('JVM');
            $table->string('TrackingCode', 100)->nullable();
            $table->string('AirwayBillNumber', 100)->nullable();
            $table->integer('PaymentStatus')->nullable();
            $table->integer('DeliveryID')->nullable();
            $table->integer('UpdatedDate')->nullable()->index('UpdatedDate');
            $table->integer('UserID')->nullable()->index('UserID');
            $table->text('Message')->nullable();
            $table->integer('SaturdayDelivery')->nullable();
            $table->integer('UPSAccessPointAddress')->nullable();
            $table->integer('TrackingSent')->nullable()->index('TrackingSent');
            $table->text('CSNotes')->nullable();
            $table->integer('DoctorAddressID')->nullable();
            $table->string('Company', 50)->nullable();
            $table->integer('CustomerID')->nullable();

            $table->index(['ClientID', 'ReferenceNumber'], 'ClientIDReferenceNumber');
            $table->index(['DCountryCode', 'DPostcode', 'DAddress1'], 'DCountryCodeDPostcodeDAddress1');
            $table->index(['TrackingSent', 'PrescriptionID'], 'track_pre');
            $table->index(['Name', 'Surname', 'DOB'], 'name_surname_dob');
            $table->index(['Status', 'CreatedDate', 'PrescriptionID'], 'status_createddate_prsid_asc');
            $table->index(['Status', 'PrescriptionID'], 'status_prsid_asc');
            $table->index(['Status', 'CreatedDate'], 'StatusCreatedDate');
            $table->index(['Status', 'ReferenceNumber'], 'StatusReferenceNumber');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('prescription');
    }
};
