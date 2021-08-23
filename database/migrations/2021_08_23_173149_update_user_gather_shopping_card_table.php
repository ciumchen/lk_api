<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUserGatherShoppingCardTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('gather_shopping_card', function (Blueprint $table) {
            $table->integer('type')->default(1)->comment('操作类型：1购物卡余额添加，2购物卡余额扣除');
            $table->string('name')->default('拼团中奖')->comment('操作类型名称');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('gather_shopping_card', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->dropColumn('name');
        });
    }
}
