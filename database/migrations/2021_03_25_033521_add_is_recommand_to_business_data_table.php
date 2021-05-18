<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsRecommandToBusinessDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('business_data', function (Blueprint $table) {
            $table->tinyInteger('is_recommend')->default(0)->comment('是否推荐，0不推荐，1推荐');
            $table->integer('sort')->default(0)->comment('排序，数字越大越靠前');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('business_data', function (Blueprint $table) {
            $table->dropColumn(['is_recommend', 'sort']);
        });
    }
}
