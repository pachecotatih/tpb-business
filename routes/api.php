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

Route::post('forgot-password', 'App\Http\Controllers\UserController@forgotPassword')->name('password.request');
Route::post('reset-password', 'App\Http\Controllers\UserController@resetPassword')->name('password.update');
Route::get('reset-password/{token}', 'App\Http\Controllers\UserController@sendResetPasswordScreen')->name('password.reset');

Route::middleware(['jwt.auth', 'ensure.user.header'])->group(function () {
    Route::group(['prefix' => 'user'], function () {
        Route::get('/', 'App\Http\Controllers\UserController@index');
        Route::put('/', 'App\Http\Controllers\UserController@update');
        Route::post('/change-password', 'App\Http\Controllers\UserController@changePasswordLogged');
    });

    Route::group(['prefix' => 'fluxocaixa'], function () {
        Route::get('/', 'App\Http\Controllers\FluxoCaixaController@index');
        Route::post('/', 'App\Http\Controllers\FluxoCaixaController@store');
        Route::get('/{uid}', 'App\Http\Controllers\FluxoCaixaController@show');
        Route::put('/{uid}', 'App\Http\Controllers\FluxoCaixaController@update');
        Route::delete('/{uid}', 'App\Http\Controllers\FluxoCaixaController@destroy');
    });

    Route::group(['prefix' => 'cliente'], function () {
        Route::get('/', 'App\Http\Controllers\ClienteController@index');
        Route::post('/', 'App\Http\Controllers\ClienteController@store');
        Route::get('/{uid}', 'App\Http\Controllers\ClienteController@show');
        Route::put('/{uid}', 'App\Http\Controllers\ClienteController@update');
        Route::delete('/{uid}', 'App\Http\Controllers\ClienteController@destroy');
    });

    Route::group(['prefix' => 'servico'], function () {
        Route::get('/', 'App\Http\Controllers\ServicoController@index');
        Route::post('/', 'App\Http\Controllers\ServicoController@store');
        Route::get('/{uid}', 'App\Http\Controllers\ServicoController@show');
        Route::put('/{uid}', 'App\Http\Controllers\ServicoController@update');
        Route::put('/{uid}/ativo', 'App\Http\Controllers\ServicoController@updateAtivo');
        Route::delete('/{uid}', 'App\Http\Controllers\ServicoController@destroy');
    });

    Route::group(['prefix' => 'agendamento'], function () {
        Route::get('/', 'App\Http\Controllers\AgendamentoController@index');
        Route::post('/', 'App\Http\Controllers\AgendamentoController@store');
        Route::get('/{uid}', 'App\Http\Controllers\AgendamentoController@show');
        Route::put('/{uid}', 'App\Http\Controllers\AgendamentoController@update');
        Route::delete('/{uid}', 'App\Http\Controllers\AgendamentoController@destroy');
    });

    Route::post('logout', 'App\Http\Controllers\UserController@logout');
});
