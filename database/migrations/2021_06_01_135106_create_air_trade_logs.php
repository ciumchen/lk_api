<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAirTradeLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('air_trade_logs', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->string('seat_code', 10)->nullable()->comment('舱位编码');
            $table->string('passagers', 200)->nullable()->comment('乘客信息');
            $table->string('item_id', 10)->nullable()->comment('飞机票标准商品编号');
            $table->string('contact_name', 15)->nullable()->comment('订票联系人');
            $table->string('contact_tel', 15)->nullable()->comment('联系电话');
            $table->string('date', 20)->nullable()->comment('出发日期');
            $table->string('from', 10)->nullable()->comment('起飞站点(机场)三字码');
            $table->string('to', 10)->nullable()->comment('抵达站点(机场)三字码');
            $table->string('company_code', 10)->nullable()->comment('航空公司编码');
            $table->string('flight_no', 10)->nullable()->comment('航班号');
            $table->string('order_no', 40)->nullable()->comment('order 表 -- order_no');
            $table->timestamp('created_at')->nullable()->comment('创建时间');
            $table->timestamp('updated_at')->nullable()->comment('更新时间');

            $table->index(['order_no']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('air_trade_logs');
    }
}
