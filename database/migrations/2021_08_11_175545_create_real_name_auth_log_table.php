<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRealNameAuthLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('real_name_auth_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('uid')->nullable()->comment('users表 -- id');
            $table->unsignedInteger('day')->nullable()->default(0)->comment('修改日期');
            $table->unsignedInteger('second')->nullable()->default(0)->comment('次数');
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
        Schema::dropIfExists('real_name_auth_log');
    }
}
