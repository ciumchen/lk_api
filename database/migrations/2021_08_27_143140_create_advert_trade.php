<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdvertTrade extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('advert_trade', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedInteger('uid')->default(0)->comment('用户id');
            $table->unsignedInteger('business_uid')->default(0)->comment('商户id');
            $table->unsignedInteger('oid')->default(0)->comment('order表 id');
            $table->string('order_no', 30)->default('')->comment('订单编号');
            $table->unsignedDecimal('profit_ratio', 6, 3)->default(0.00)->comment('让利比列');
            $table->unsignedDecimal('price', 10, 2)->default(0.00)->comment('金额');
            $table->unsignedDecimal('profit_price', 10, 2)->default(0.00)->comment('实际让利金额');
            $table->tinyInteger('status')->default(1)->comment('状态：0 待处理；1 成功；2 失败');
            $table->timestamp('created_at')->nullable()->comment('创建时间');
            $table->timestamp('updated_at')->nullable()->comment('更新时间');

            $table->index(['uid', 'oid', 'order_no']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('advert_trade');
    }
}
