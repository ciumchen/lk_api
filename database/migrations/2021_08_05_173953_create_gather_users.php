<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGatherUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gather_users', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedInteger('gid')->default(0)->comment('gather表 id');
            $table->unsignedInteger('uid')->default(0)->comment('用户id');
            $table->tinyInteger('status')->default(1)->comment('用户状态：0 异常；1 正常');
            $table->tinyInteger('type')->default(0)->comment('是否中奖：0 否；1 是');
            $table->timestamp('created_at')->nullable()->comment('创建时间');
            $table->timestamp('updated_at')->nullable()->comment('更新时间');

            $table->index(['gid', 'uid']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('gather_users');
    }
}
