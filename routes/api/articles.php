<?php

/* 公告列表 */
Route::get('affiche-list', 'Articles\AfficheController@getList');
/* 公告详情 */
Route::get('affiche-details', 'Articles\AfficheController@getDetails');
