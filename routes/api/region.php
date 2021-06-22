<?php
/** 区域代理 **/

//获取用户代理信息
Route::any('get-node', 'Site\RegionUserController@getNode');

//获取市级代理信息
Route::any('get-city', 'Site\RegionUserController@getCity');

//获取区级代理信息
Route::any('get-district', 'Site\RegionUserController@getDistrict');
