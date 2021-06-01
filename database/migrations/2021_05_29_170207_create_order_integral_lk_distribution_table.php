<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderIntegralLkDistributionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_integral_lk_distribution', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->unsignedInteger('day')->nullable()->default(0)->comment('控制录单日期');
            $table->unsignedInteger('switch')->nullable()->default(0)->comment('释放开关默认0表示未释放,1表示释放');
            $table->unsignedDecimal('count_lk',8,2)->nullable()->default(0)->comment('lk统计');
            $table->unsignedDecimal('count_profit_price',8,2)->nullable()->default(0)->comment('录单累计实际让利金额');

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
        Schema::dropIfExists('order_integral_lk_distribution');
    }
}
