<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRebateDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rebate_data', function (Blueprint $table) {
            $table->id();
            $table->date('day')->unique()->comment('返佣日期');
            $table->decimal('consumer', 18, 2)->default(0)->comment('消费者返佣（元）');
            $table->decimal('business',18, 2)->default(0)->comment('商家');
            $table->decimal('welfare',18, 2)->default(0)->comment('公益');
            $table->decimal('share',18, 2)->default(0)->comment('分享');
            $table->decimal('market',18, 2)->default(0)->comment('市商');
            $table->decimal('platform',18, 2)->default(0)->comment('平台');
            $table->unsignedInteger('people')->default(0)->comment('消费人数');
            $table->unsignedInteger('join_consumer')->default(0)->comment('消费者参与人数');
            $table->unsignedInteger('join_business')->default(0)->comment('商家参与人数');
            $table->unsignedInteger('new_business')->default(0)->comment('新增商家');
            $table->decimal('total_consumption',18, 2)->default(0)->comment('总消费金额');
            $table->decimal('consumer_lk_iets',18, 8)->default(0)->comment('消费者单个LK分配IETS数量');
            $table->decimal('business_lk_iets',18, 8)->default(0)->comment('商家单个LK分配IETS数量');
            $table->unsignedTinyInteger('status')->default(1)->comment('是否返佣，1未返佣，2已返佣');
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
        Schema::dropIfExists('rebate_data');
    }
}
