<?php

/**
 * GET /v1/get-statistics 获取统计信息.
 */
Route::get('get-statistics', 'Statistics\StatisticsController@getStatistics');
//获取今日消费金额和昨日消费金额统计
Route::get('getNewStatistics', 'Statistics\StatisticsController@getNewStatistics');

//获取今日消费金额和昨日消费金额统计
Route::any('shRlCount', 'Statistics\StatisticsController@shRlCount');


