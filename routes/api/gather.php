<?php
/** 拼团 **/

//获取拼团
Route::any('gather-info', 'Gather\AttendGatherController@getGatherInfo');
//参加拼团
Route::any('add-gather', 'Gather\AttendGatherController@addGatherUser');
