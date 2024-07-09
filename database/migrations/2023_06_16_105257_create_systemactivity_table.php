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
        Schema::create('systemactivity', function (Blueprint $table) {
            $table->bigIncrements('SystemActivityID');
            $table->integer('UserID')->nullable()->index('UserID');
            $table->integer('ReferenceID')->nullable()->index('PrescriptionID');
            $table->string('Name', 200)->nullable();
            $table->string('Action', 200)->nullable();
            $table->text('Arguments')->nullable();
            $table->integer('Type')->nullable()->index('Type')->comment('1 - scan errors, 2 - import log, 3 - item logs, 4 - batch logs, 5 - product logs, 6 - cautionary and advisory labels, 7 - additional information');
            $table->integer('Status')->nullable()->index('Status');
            $table->timestamp('CreatedAt')->useCurrent();
            $table->timestamp('UpdatedAt')->useCurrentOnUpdate()->useCurrent();
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
        Schema::dropIfExists('systemactivity');
    }
};
