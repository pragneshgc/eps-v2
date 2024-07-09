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
        Schema::create('pxpuser', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('pharmacy_id')->nullable();
            $table->integer('pharmacy_id2')->nullable();
            $table->unsignedInteger('role')->default(10);
            $table->string('name');
            $table->string('surname');
            $table->string('email', 50)->unique('email');
            $table->string('password');
            $table->rememberToken();
            $table->string('code')->nullable()->default('');
            $table->string('token')->nullable()->default('');
            $table->timestamp('created_at')->nullable();
            $table->softDeletes();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pxpuser');
    }
};
