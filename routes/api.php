<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Configuracion de cabeceras
/*header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method, authorization, content-type");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");*/

/*Rutas publicas*/

/*Rutas para Iniciar sesion y registrarse*/
Route::post('login', 'ApiController@login');
Route::post('register', 'ApiController@register');

/*Ruta para consultar todos los productos*/
Route::get('products', 'ProductController@index');

Route::group(['middleware' => 'auth.jwt'], function () {

    /*Rutas para gestionar ordenes*/
    Route::post('orderRegister', 'OrderController@store');
    Route::get('orders', 'OrderController@index');
    Route::get('orderDetail/{id}', 'OrderController@showDetail');
    Route::post('retryPayment', 'OrderController@retryPayment');

});
