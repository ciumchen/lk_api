<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderAirRefund extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_air_refund', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->string('trade_no', 30)->nullable()->comment('订单主编号');
            $table->unsignedInteger('return_type')->default(0)->comment('退票类型:3-退票');
            $table->string('order_nos', 200)->nullable()->comment('订单子单编号集合');
            $table->timestamp('created_at')->nullable()->comment('创建时间');
            $table->timestamp('updated_at')->nullable()->comment('更新时间');

            $table->index(['trade_no']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_air_refund');
    }
}
