<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserUpdatePhoneLogSdTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_update_phone_log_sd', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id')->nullable()->comment('users表 -- id');
            $table->unsignedInteger('time')->nullable()->default(0)->comment('修改时间');
            $table->string('edit_to_phone', 35)->nullable()->comment('修改的手机号');
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
        Schema::dropIfExists('user_update_phone_log_sd');
    }
}
