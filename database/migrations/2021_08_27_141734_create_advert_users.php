<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdvertUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('advert_users', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedInteger('uid')->default(0)->comment('用户id');
            $table->unsignedDecimal('award', 10, 2)->default(0.00)->comment('奖励数量');
            $table->string('package_name', 30)->default('')->comment('渠道包名');
            $table->tinyInteger('type')->default(0)->comment('类型：1 幸运抽奖；2 答题；3 猜成语；4 刮刮乐；10 拆红包；11 拆红包翻倍；12 签到；13 签到翻倍；14 任务奖励；15 任务翻倍奖励；16 蘑菇；17 集碎片；18 达标兑换；19 步数兑换；20 气泡兑换；21 拉新；22 拉活');
            $table->tinyInteger('status')->default(1)->comment('状态：0 异常；1 正常');
            $table->unsignedInteger('unique_id')->default(0)->comment('唯一标识');
            $table->timestamp('created_at')->nullable()->comment('创建时间');
            $table->timestamp('updated_at')->nullable()->comment('更新时间');

            $table->index(['uid']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('advert_users');
    }
}
