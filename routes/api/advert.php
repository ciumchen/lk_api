<?php
/** 广告 **/

//获取广告收入
Route::any('add-income', 'Advert\AdvertIsementController@addUsereIncome');
//获取用户广告奖励
Route::any('user-award', 'Advert\AdvertIsementController@getUsereAdvert');
//用户兑换广告奖励
Route::any('take-award', 'Advert\AdvertIsementController@addTakeAward');
//拼团广告记录
Route::any('gather-advert', 'Advert\AdvertIsementController@addGatherAdvert');
