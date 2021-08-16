<?php
/** 拼团 **/

//获取拼团
Route::any('gather-info', 'Gather\AttendGatherController@getGatherInfo');
//参加拼团
Route::any('add-gather', 'Gather\AttendGatherController@addGatherUser');
//获取用户来拼金
Route::any('user-gold', 'Gather\AttendGatherController@getGatherGold');
//获取用户拼团信息
Route::any('user-gather', 'Gather\GatherUserController@getGatherInfo');
//获取用户拼团获奖信息
Route::any('gather-lottery', 'Gather\GatherUserController@getGatherLottery');
//获取用户拼团来拼金可提现总额
Route::any('advance-gold', 'Gather\GatherUserController@getAdvanceGold');
