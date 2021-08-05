<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGather extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gather', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->tinyInteger('type')->default(0)->comment('拼团类型');
            $table->tinyInteger('status')->default(0)->comment('状态：0 停止；1 开启；');
            $table->timestamp('created_at')->nullable()->comment('创建时间');
            $table->timestamp('updated_at')->nullable()->comment('更新时间');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('gather');
    }
}
