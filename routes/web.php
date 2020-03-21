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


Route::prefix('/payment')->group( function () {
    Route::get('/', function(){
        return view('payment');
    });
    Route::get('method', 'Controller@paymentMethod');
    Route::post('make', 'Controller@makePayment');

    Route::get('create-link', 'Controller@createLink');
});
