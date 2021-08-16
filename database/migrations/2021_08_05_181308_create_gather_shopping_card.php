<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGatherShoppingCard extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gather_shopping_card', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedInteger('gid')->default(0)->comment('gather表 id');
            $table->unsignedInteger('uid')->default(0)->comment('用户id');
            $table->unsignedInteger('guid')->default(0)->comment('gather_users表 id');
            $table->unsignedDecimal('money', 10, 2)->default(0.00)->comment('购物卡金额');
            $table->tinyInteger('status')->default(1)->comment('是否允许使用：0 否；1 是');
            $table->timestamp('created_at')->nullable()->comment('创建时间');
            $table->timestamp('updated_at')->nullable()->comment('更新时间');

            $table->index(['gid', 'uid', 'guid']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('gather_shopping_card');
    }
}
