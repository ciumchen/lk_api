<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWithdrawLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('withdraw_logs', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('uid');
            $table->integer('assets_type_id')->comment('资金类型ID');
            $table->string('assets_type', 16)->comment('资金类型');
            $table->string('address', 42)->comment('地址');
            $table->decimal('amount', 20, 8)->default(0)->comment('数量');
            $table->decimal('fee', 20, 8)->default(0)->comment('手续费');
            $table->string('tx_hash', 66)->unique()->nullable()->comment('交易HASH');
            $table->tinyInteger('status')->comment('1默认 2成功 3审核中 4拒绝');
            $table->string('ip', 16)->comment('ip');
            $table->string('remark', 64)->comment('备注');
            $table->text('user_agent')->nullable();
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
        Schema::dropIfExists('withdraw_logs');
    }
}
