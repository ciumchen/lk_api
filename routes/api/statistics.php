<?php

/**
 * GET /v1/get-statistics 获取统计信息.
 */
Route::get('get-statistics', 'Statistics\StatisticsController@getStatistics');
//获取今日消费金额和昨日消费金额统计
Route::get('getNewStatistics', 'Statistics\StatisticsController@getNewStatistics');

//获取今日消费金额和昨日消费金额统计
Route::any('shRlCount', 'Statistics\StatisticsController@shRlCount');

//获取今日排队和剩余排队订单的消费金额的让利比例的统计（5%-10%-20%）
Route::any('getGiveOderPrice', 'Statistics\StatisticsController@getGiveOderPrice');

//获取各种类型订单让利比例的统计（5%-10%-20%）
Route::any('getOrderRlbl', 'Statistics\StatisticsController@getOrderRlbl');

//查询是否开始美团活动
Route::any('getMeiTuanHdProfitRatio', 'Statistics\StatisticsController@getMeiTuanHdProfitRatio');



