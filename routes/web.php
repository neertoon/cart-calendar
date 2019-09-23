<?php

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

//Route::get('/', function () {
//    return view('welcome');
//});

//Route::get('user/{id}', 'Calendar@index');

Route::get('/', 'Calendar@login');

Route::get('/cart', 'Calendar@index');

Route::get('/test', 'Calendar@test');
