<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssetsLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('assets_logs', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedInteger('assets_type_id')->comment('资产类型id');
            $table->string('assets_name',20)->comment('资产名称');
            $table->unsignedInteger('uid')->comment('uid');
            $table->string('operate_type',80)->comment('操作类型');
            $table->decimal('amount',18,8)->comment('变动数量');
            $table->decimal('amount_before_change',18,8)->comment('变动前数量');
            $table->string('tx_hash',66)->nullable()->unique()->comment('交易hash');
            $table->string('ip',15)->nullable()->comment('ip');
            $table->text('user_agent')->comment('ua');
            $table->string('remark',100)->nullable()->comment('备注');
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
        Schema::dropIfExists('assets_logs');
    }
}
