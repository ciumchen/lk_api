<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeleteUserJfLkTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('delete_user_jf_lk', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('uid')->nullable()->comment('uid');
            $table->tinyInteger('type')->default(0)->comment('操作类型:1删除消费者积分，2删除商家积分');
            $table->decimal('amount_before_change',18,2)->nullable()->comment('删除前积分数量');
            $table->decimal('amount',18,2)->nullable()->comment('删除积分');
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
        Schema::dropIfExists('delete_user_jf_lk');
    }
}
