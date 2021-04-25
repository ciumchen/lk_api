<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBusinessApplyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('business_apply', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('uid')->comment('uid');
            $table->string('img',255)->comment('营业执照图片');
            $table->string('phone',25)->comment('联系电话');
            $table->string('name',25)->comment('商店名称');
            $table->string('work',64)->comment('主营业务');
            $table->string('address',128)->comment('商家地址');
            $table->string('remark',100)->nullable()->comment('备注');
            $table->unsignedInteger('status')->default(1)->comment('1审核中，2审核通过，3审核失败');
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
        Schema::dropIfExists('business_apply');
    }
}
