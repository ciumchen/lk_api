<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSignInTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sign_in', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('uid')->default(0)->comment('用户ID');
            $table->bigInteger('yx_uid')->default(0)->comment('用户在优选商城中的ID');
            $table->string('sign_date')->default('')->comment('签到日期格式 YYYYmmdd');
            $table->tinyInteger('is_add_points')->default(0)->comment('是否已经添加积分');
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
        Schema::dropIfExists('sign_in');
    }
}
