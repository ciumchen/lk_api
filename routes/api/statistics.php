<?php

/**
 * GET /v1/get-statistics 获取统计信息.
 */
Route::get('get-statistics', 'Statistics\StatisticsController@getStatistics');
