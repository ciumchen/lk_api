<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderHotelTable extends Migration
{
    
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'order_hotel',
            function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->charset = 'utf8mb4';
                $table->collation = 'utf8mb4_unicode_ci';
                $table->id('id');
                $table->string('order_no', '30')
                      ->default('')
                      ->comment('订单号');
                $table->bigInteger('user_id')
                      ->default(0)
                      ->comment('充值用户ID');
                $table->bigInteger('order_id')
                      ->default(0)
                      ->comment('订单表ID');
                $table->decimal('money')
                      ->default(0)
                      ->comment('金额');
                $table->string('trade_no', '50')
                      ->default('')
                      ->comment('接口方返回单号');
                $table->tinyInteger('pay_status')
                      ->unsigned()
                      ->default('0')
                      ->comment('平台订单付款状态:0未付款,1已付款');
                $table->tinyInteger('status')
                      ->unsigned()
                      ->default('0')
                      ->comment('充值状态:0充值中,1成功,9撤销');
                $table->string('goods_title', '80')
                      ->default('')
                      ->comment('商品名称');
                $table->string('item_id', 30)
                      ->default('')
                      ->comment('商品编号[ID]');
                /* Hotel Start */
                $table->string('customer_name', 100)
                      ->default('')
                      ->comment('入住人姓名，每个房间仅需填写1人。【多个人代表多个房间、使用逗号‘,’分隔】');
                $table->string('hotel_id', 20)->default('')->comment('酒店ID');
                $table->string('contact_name', 20)->default('')->comment('联系人姓名');
                $table->string('contact_phone', 20)->default('')->comment('联系人手机号码');
                $table->date('in_date')->nullable()->comment('入住时间');
                $table->date('out_date')->nullable()->comment('离开时间');
                $table->mediumInteger('man')->default(0)->comment('入住成人数，需和实施询价时填的一样');
                $table->time('customer_arrive_time')->nullable()->comment('客户到达时间 格式HH:mm:ss 例如09:20:30 表示早上9点20分30秒');
                $table->string('special_remarks', 50)->nullable()->comment(
                    '特殊需求 可传入多个，格式：2,8。
0 无要求
2 尽量安排无烟房
8 尽量安排大床 仅当床型为“X张大床或X张双床”时，此选项才有效
10 尽量安排双床房 仅当床型为“X张大床或X张双床”时，此选项才有效
11 尽量安排吸烟房
12 尽量高楼层
15 尽量安排有窗房
16 尽量安排安静房间
18 尽量安排相近房间'
                );
                $table->string('contact_email', 100)->default('')->comment('联系人邮箱');
                $table->mediumInteger('child_num')->default(0)->comment('入住儿童数，与实时询价时提交的应一致');
                $table->string('child_ages', 50)->default('')->comment('入住儿童的年龄，多个年龄用,分隔，与实时询价时提交的应一致');
                /* Hotel End */
                $table->dateTime('created_at')
                      ->nullable()
                      ->comment('创建时间');
                $table->dateTime('updated_at')
                      ->nullable()
                      ->comment('更新时间');
            }
        );
        DB::statement('ALTER TABLE `order_utility_bill` comment "酒店预定订单表";');
    }
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_hotel');
    }
}
