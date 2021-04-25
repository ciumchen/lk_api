<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedInteger('uid')->comment('uid');
            $table->unsignedInteger('assets_type_id')->comment('资产类型ID');
            $table->string('assets_name',50)->comment('资产名称');
            $table->decimal('amount',18,8)->default(0)->comment('数量');
            $table->decimal('freeze_amount',18,8)->default(0)->comment('冻结数量');
            $table->unique(['uid','assets_type_id']);
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
        Schema::dropIfExists('assets');
    }
}
