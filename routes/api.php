<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::post('register', 'App\Http\Controllers\UserController@store');
Route::post('login', 'App\Http\Controllers\UserController@login');
Route::post('refresh', 'App\Http\Controllers\UserController@refresh');

Route::middleware('jwt.auth')->group(function ($router) {
    Route::get('user', 'App\Http\Controllers\UserController@index');
    Route::post('logout', 'App\Http\Controllers\UserController@logout');
});
