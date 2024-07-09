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
        Schema::create('activity', function (Blueprint $table) {
            $table->bigIncrements('ActivityID');
            $table->integer('UserID')->nullable()->index('date_id');
            $table->string('Name', 200)->nullable();
            $table->integer('OrderID')->nullable()->index('order_id');
            $table->string('Date', 30)->nullable();
            $table->string('Action', 200)->nullable();
            $table->text('Arguments')->nullable();
            $table->integer('Type')->nullable();
            $table->integer('Status')->nullable();
            $table->date('Date2')->nullable()->index('date2');
            $table->integer('Hour')->nullable();
            $table->string('Min', 5)->nullable();

            $table->index(['Date2', 'Type'], 'date2_type');
            $table->index(['Date2', 'Type', 'UserID'], 'date2_type_userid');
            $table->index(['OrderID', 'ActivityID'], 'orderid_activity_desc');
            $table->index(['OrderID', 'Type', 'ActivityID'], 'orderid_type_activity_desc');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('activity');
    }
};
