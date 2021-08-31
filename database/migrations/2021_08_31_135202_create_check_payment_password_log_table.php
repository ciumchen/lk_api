<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCheckPaymentPasswordLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('check_payment_password_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('uid')->nullable()->comment('uid');
            $table->unsignedInteger('time')->default(0)->comment('校验时间');
            $table->tinyInteger('num')->default(0)->comment('次数');
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
        Schema::dropIfExists('check_payment_password_log');
    }
}
