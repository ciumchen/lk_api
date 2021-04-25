<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssetsTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('assets_type', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->string('contract_address',42)->unique()->nullable()->comment('合约地址');
            $table->string('assets_name',20)->comment('资产名称');
            $table->unsignedTinyInteger('recharge_status')->default(1)->comment('是否可充值，1可充值，2不能充值');
            $table->unsignedTinyInteger('can_withdraw')->default(2)->comment('是否能提现，1能，2不能');
            $table->decimal('withdraw_fee',4,2)->default(0)->comment('提现手续费（%）');
            $table->decimal('large_withdraw_amount',18,8)->default(0)->comment('提现审核额度');
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
        Schema::dropIfExists('assets_type');
    }
}
