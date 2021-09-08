<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGwkLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gwk_log', function (Blueprint $table) {
            $table->id();
            $table->string('day',20)->default('')->comment('日期')->index();
            $table->unsignedDecimal('count_profit_price',12,2)->nullable()->default(0)->comment('录单累计实际让利金额');
            $table->unsignedDecimal('price_5',12,2)->nullable()->default(0)->comment('订单消费金额5%让利比例的累计');
            $table->unsignedDecimal('price_10',12,2)->nullable()->default(0)->comment('订单消费金额10%让利比例的累计');
            $table->unsignedDecimal('price_20',12,2)->nullable()->default(0)->comment('订单消费金额20%让利比例的累计');
            $table->unsignedDecimal('other_price',12,2)->nullable()->default(0)->comment('订单消费金额其他让利比例的累计');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('gwk_log');
    }
}
