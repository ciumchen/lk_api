<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAuthLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('auth_logs', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->bigIncrements('id');
            $table->unsignedInteger('uid')->comment('用户id');
            $table->string('id_card',19)->comment('身份证号');
            $table->string('name',10)->comment('姓名');
            $table->text('id_card_img')->comment('身份证照片URL');
            $table->text('id_card_people_img')->comment('手持身份证照片URL');
            $table->integer('status')->default(1)->comment('1审核中，2审核通过，3审核失败');
            $table->string('msg',255)->nullable()->comment('备注');
            $table->index('status');
            $table->unique('uid');
            $table->index('id_card');
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
        Schema::dropIfExists('auth_logs');
    }
}
