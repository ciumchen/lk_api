<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGwkZfOperationLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gwk_zf_operation_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('oid')->index()->comment('oid');
            $table->string('order_no', 40)->default('')->comment('订单号');
            $table->integer('status')->default(1)->comment('1未处理，2处理中，3处理完成');
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
        Schema::dropIfExists('gwk_zf_operation_log');
    }
}
