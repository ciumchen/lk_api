<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIntegralLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('integral_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('uid')->comment('uid');
            $table->string('operate_type',80)->comment('操作类型');
            $table->decimal('amount',18,2)->comment('变动数量');
            $table->decimal('amount_before_change',18,2)->comment('变动前数量');
            $table->unsignedTinyInteger('role')->default(1)->index()->comment('1普通用户，2商家');
            $table->string('ip',15)->nullable()->comment('ip');
            $table->text('user_agent')->nullable()->comment('ua');
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
        Schema::dropIfExists('integral_log');
    }
}
