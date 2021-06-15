<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/', function () {
    return view('welcome');
});
Route::get('register', 'User\RegisterController');
Route::get('download-app', 'User\RegisterController@downloadApp');
Route::any('clear', function () {
    Artisan::call('cache:clear');
    echo '缓存文件已清理' . '<br/>';
    Artisan::call('config:clear');
    echo '配置缓存已清理' . '<br/>';
    Artisan::call('route:clear');
    echo '路由缓存已清理' . '<br/>';
    Artisan::call('view:clear');
    echo '视图缓存已清理' . '<br/>';
});
