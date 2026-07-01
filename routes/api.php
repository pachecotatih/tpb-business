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

Route::middleware('jwt.auth')->group(function () {
    Route::group(['prefix' => 'user'], function () {
        Route::get('/', 'App\Http\Controllers\UserController@index');
        Route::put('/', 'App\Http\Controllers\UserController@update');
    });

    Route::group(['prefix' => 'fluxocaixa'], function () {
        Route::post('/', 'App\Http\Controllers\FluxoCaixaController@store');
        Route::put('/{uid}', 'App\Http\Controllers\FluxoCaixaController@update');
    });

    Route::post('logout', 'App\Http\Controllers\UserController@logout');
});
