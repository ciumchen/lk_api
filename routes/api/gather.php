<?php
/** 拼团 **/

//获取拼团
Route::any('gather-info', 'Gather\AttendGatherController@getGatherInfo');
//参加拼团
Route::any('add-gather', 'Gather\AttendGatherController@addGatherUser');
//获取用户来拼金
Route::any('user-gold', 'Gather\AttendGatherController@getGatherGold');
