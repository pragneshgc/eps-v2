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
        Schema::create('authorizationcode', function (Blueprint $table) {
            $table->bigIncrements('AuthorizationCodeID');
            $table->integer('UserID')->default(0);
            $table->char('Code', 30)->default('0');
            $table->integer('Type')->default(1)->comment('1 - Reprint');
            $table->timestamp('CreatedAt')->useCurrent()->comment('When the code was created');
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
        Schema::dropIfExists('authorizationcode');
    }
};
