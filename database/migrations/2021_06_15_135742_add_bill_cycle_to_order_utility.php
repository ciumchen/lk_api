<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBillCycleToOrderUtility extends Migration
{
    
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_utility_bill', function (Blueprint $table) {
            $table->string('bill_cycle', 50)
                  ->default('')
                  ->comment('账单账期');
            $table->string('contract_no', 50)
                  ->default('')
                  ->comment('账单合同号');
            $table->string('content_id', 20)
                  ->default('')
                  ->comment('账期标识');
            $table->string('item4')
                  ->default('')
                  ->comment('扩展字段');
            $table->tinyInteger('prepaid_flag')
                  ->default(0)
                  ->comment('账号类型：1是预付费 2后付费');
            $table->decimal('penalty')
                  ->default(0.00)
                  ->comment('滞纳金');
            $table->decimal('balance')
                  ->default(0.00)
                  ->comment('余额');
            $table->decimal('pay_amount')
                  ->default(0.00)
                  ->comment('应缴金额');
            $table->string('item_id')
                  ->default('')
                  ->comment('标准商品ID,接口返回');
        });
    }
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_utility_bill', function (Blueprint $table) {
            $table->dropColumn('bill_cycle');
            $table->dropColumn('contract_no');
            $table->dropColumn('content_id');
            $table->dropColumn('item4');
            $table->dropColumn('prepaid_flag');
            $table->dropColumn('penalty');
            $table->dropColumn('balance');
            $table->dropColumn('pay_amount');
            $table->dropColumn('item_id');
        });
    }
}
